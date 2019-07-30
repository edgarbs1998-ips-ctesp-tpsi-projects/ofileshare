<?php

class Uploader {
    public $options;
    public $fileUpload = null;

    function __construct ( $options = null ) {
        // get logged in user details
        $user = User::getInstance ( );
        $userId = null;
        if ( $user->isLogged ( ) ) {
            $userId = $user->id;
        }

        // default options
        $this->options = array (
            "upload_dir"              => DOC_ROOT . "/files/",
            "upload_url"              => WEB_ROOT . "/files/",
            "param_name"              => "files",
            "delete_hash"             => "",
            "max_file_size"           => User::getMaxUploadFilesize ( $userId ),
            "min_file_size"           => 1,
            "discard_aborted_uploads" => true,
            "max_chunk_size"          => 0,
            "folder_id"               => 0,
            "user_id"                 => $userId,
            "fail_zero_bytes"         => true,
        );

        if ( $options ) {
            $this->options = array_replace_recursive ( $this->options, $options );
        }
    }

    public function handleFileUpload ( $uploadedFile, $name, $size, $type, $error, $index = null, $contentRange = null, $chunkTracker = null ) {
        $fileUpload        = new stdClass ( );
        $fileUpload->name  = stripslashes ( $name );
        $fileUpload->size  = intval ( $size );
        $fileUpload->type  = $type;
        $fileUpload->error = null;

        // save file locally is chunked upload
        if ( $contentRange ) {
            $localTempStore     = self::getLocalTempStorePath ( );
            $tmpFilename        = md5 ( $fileUpload->name );
            $tmpFilePath        = $localTempStore . $tmpFilename;

            // if first chunk
            if ( $contentRange [ 1 ] == 0 ) {
                // ensure the tmp file does not already exist
                if ( file_exists ( $tmpFilePath ) ) {
                    unlink ( $tmpFilePath );
                }

                // clean up any old chunks while we're here
                $this->cleanLeftOverChunks ( );
            }

            // ensure we have the chunk
            if ( $uploadedFile && file_exists ( $uploadedFile ) ) {
                // multipart/formdata uploads (POST method uploads)
                $fp = fopen ( $uploadedFile, "r" );
                file_put_contents ( $tmpFilePath, $fp, FILE_APPEND );
                fclose ( $fp );

                // check if this is not the last chunk
                if ( $contentRange [ 3 ] != filesize ( $tmpFilePath ) ) {
                    // exit
                    return $fileUpload;
                }

                // otherwise assume we have the whole file
                $uploadedFile       = $tmpFilePath;
                $fileUpload->size   = filesize ( $tmpFilePath );
            } else {
                // exit
                return $fileUpload;
            }
        }

        $fileUpload->error = $this->hasError ( $uploadedFile, $fileUpload, $error );
        if ( !$fileUpload->error ) {
            if ( strlen ( trim ( $fileUpload->name ) ) == 0 ) {
                $fileUpload->error = "Filename not found.";
            }
        } elseif ( intval ( $size ) == 0 && $this->options [ "fail_zero_bytes" ] == true ) {
            $fileUpload->error = "File received has zero size. This is likely an issue with the maximum permitted size within PHP";
        } elseif ( intval ( $size ) > $this->options [ "max_file_size" ] ) {
            $fileUpload->error = "File received is larger than permitted. (max " . Functions::formatSize ( $this->options [ "max_file_size" ] ) . ")";
        }
        
        if ( !$fileUpload->error && $fileUpload->name ) {
            $fileUpload = $this->moveIntoStorage ( $fileUpload, $uploadedFile );
        }

        // no error, add success html
        if ( $fileUpload->error === null ) {
			$fileUpload->url_html               = "&lt;a href=&quot;" . $fileUpload->url . "&quot; target=&quot;_blank&quot; title=&quot;Download file from " . CONFIG_SITE_NAME . "&quot;&gt;Download " . $fileUpload->name . " from " . CONFIG_SITE_NAME . "&lt;/a&gt;";
			$fileUpload->url_bbcode             = "[url]" . $fileUpload->url . "[/url]";
            $fileUpload->success_result_html    = self::generateSuccessHtml ( $fileUpload );
        } else {
            $fileUpload->error_result_html = self::generateErrorHtml ( $fileUpload );
        }

        return $fileUpload;
    }

    public function moveIntoStorage ( $fileUpload, $tmpFile, $keepOriginal = false ) {
        $user = User::getInstance ( );

        if ( $fileUpload->name [ 0 ] === "." ) {
            $fileUpload->name = substr ( $fileUpload->name, 1 );
        }
        $fileUpload->name = trim ( $fileUpload->name );
        if ( strlen ( $fileUpload->name ) == 0 ) {
            $fileUpload->name = date ( "Ymdhi" );
        }
        $parts     = explode ( ".", $fileUpload->name );
        $lastPart  = end ( $parts );
        $extension = strtolower ( $lastPart );

        // figure out upload type
        $file_size = 0;

        // store the actual file
        $rs             = $this->storeFile ( $fileUpload, $tmpFile, $keepOriginal );
        $file_size      = $rs [ "file_size" ];
        $file_path      = $rs [ "file_path" ];
        $fileUpload     = $rs [ "fileUpload" ];
        $newFilename    = $rs [ "newFilename" ];
        $fileHash       = $rs [ "fileHash" ];

        // get database connection
        $db = Database::getInstance ( );

        // check filesize uploaded matches tmp uploaded
        if ( $file_size == $fileUpload->size && !$fileUpload->error ) {
            $fileUpload->url = $this->options [ "upload_url" ] . rawurlencode ( $fileUpload->name );

            // insert into the db
            $fileUpload->size        = $file_size;
            $fileUpload->delete_url  = "~d?" . $this->options  [ "delete_hash" ];
            $fileUpload->info_url    = "~i?" . $this->options [ "delete_hash" ];
            $fileUpload->delete_type = "DELETE";
            $fileUpload->delete_hash = $this->options [ "delete_hash" ];

            // create delete hash, make sure it's unique
            $deleteHash = md5 ( $fileUpload->name . $user->username . microtime ( ) );

            // setup folder id for file
            $folderId = null;
            if ( ( int ) $this->options [ "folder_id" ] > 0 && ( int ) $this->options [ "user_id" ] > 0 ) {
                // make sure the current user owns the folder
                $db->query ( "CALL sp_user_owns_folder ( :user_id, :folder_id )", array ( "user_id" => ( int ) $this->options [ "user_id" ], "folder_id" => ( int ) $this->options [ "folder_id" ] ) );
                $validFolder = $db->getRow ( );
                if ( $validFolder ) {
                    $folderId = ( int ) $this->options [ "folder_id" ];
                }
            }
            if ( ( int ) $folderId == 0 ) {
                $folderId = null;
            }

            // make sure the original filename is unique in the selected folder
            $filename = $fileUpload->name;
            if ( ( int ) $this->options [ "user_id" ] > 0 ) {
                $foundExistingFile = 1;
                $tracker = 2;
                while ( $foundExistingFile >= 1 ) {
                    $db->query ( "CALL sp_file_duplicate_name ( :user_id, :folder_id, :file_name, null )", array ( "user_id" => ( int ) $this->options [ "user_id" ], "folder_id" => $folderId, "file_name" => $filename ) );
                    $foundExistingFile = ( int ) $db->getValue( );
                    if ( $foundExistingFile >= 1 ) {
                        $filename = substr ( $fileUpload->name, 0, strlen ( $fileUpload->name ) - strlen ( $extension ) - 1 ) . " (" . $tracker . ")." . $extension;
                        ++$tracker;
                    }
                }
            }
            $fileUpload->name = $filename;
            $fileUpload->hash = false;
            if ( file_exists ( $tmpFile ) ) {
                $fileUpload->hash = md5_file ( $tmpFile );
            }
            
            if ( !$fileUpload->error ) {
                try {
                    $db->query ( "CALL sp_file_add (    :user_id,
                                                        :file_name,
                                                        :file_type,
                                                        :file_extension,
                                                        :file_size,
                                                        :file_path,
                                                        :file_hash,
                                                        :delete_hash,
                                                        :folder_id,
                                                        :upload_ip )",
                                array (
                                    "user_id"           => ( int ) $this->options [ "user_id" ],
                                    "file_name"         => $fileUpload->name,
                                    "file_type"         => $fileUpload->type,
                                    "file_extension"    => strtolower ( $extension ),
                                    "file_size"         => $fileUpload->size,
                                    "file_path"         => substr ( $file_path, 0, strlen ( $this->options [ "upload_dir" ] ) ) == $this->options [ "upload_dir" ] ? substr ( $file_path, strlen ( $this->options [ "upload_dir" ] ) ) : $file_path,
                                    "file_hash"         => $fileHash,
                                    "delete_hash"       => $deleteHash,
                                    "folder_id"         => $folderId,
                                    "upload_ip"         => Functions::getUserIpAddress ( ),
                                )
                    );

                    $fileId = $db->insertId ( );
                    $fileTags = File::getTagArrFromString ( $fileUpload->name );
                    foreach ( $fileTags AS $tag ) {
                        $db->query ( "CALL sp_file_add_tag ( :file_id, :tag )", array ( "file_id" => $fileId, "tag" => $tag ) );
                    }
                } catch ( APPException $ex ) {
                    $fileUpload->error = "Failed adding file to database. " . $ex->getMessage ( );
                }
    
                if ( !$fileUpload->error ) {
                    // create short url
                    $tracker    = 1;
                    $shortUrl   = File::createShortUrlPart ( $tracker . $fileId );
                    $fileTmp    = File::loadByShortUrl ( $shortUrl );
                    while ( $fileTmp ) {
                        $shortUrl   = File::createShortUrlPart ( $tracker . $fileId );
                        $fileTmp    = File::loadByShortUrl ( $shortUrl );
                        ++$tracker;
                    }
    
                    // update short url
                    File::updateShortUrl ( $fileId, $shortUrl );
    
                    // update fileUpload with file location
                    $file                       = File::loadByShortUrl ( $shortUrl );
                    $fileUpload->url            = $file->getFullShortUrl ( );
                    $fileUpload->delete_url     = $file->getDeleteUrl ( );
                    $fileUpload->info_url       = $file->getInfoUrl ( );
                    $fileUpload->stats_url      = $file->getStatisticsUrl ( );
                    $fileUpload->delete_hash    = $file->fil_delete_hash;
                    $fileUpload->short_url      = $shortUrl;
                    $fileUpload->file_id        = $file->fil_id;
                }
            }
        } elseif ( $this->options [ "discard_aborted_uploads" ] ) {
            @unlink ( $file_path );
            @unlink ( $tmpFile );
            if ( !isset ( $fileUpload->error ) ) {
                $fileUpload->error = "General upload error, please contact support. Expected size: " . $file_size . ". Received size: " . $fileUpload->size . ".";
            }
        }

        return $fileUpload;
    }

    public function storeFile ( $fileUpload, $tmpFile, $keepOriginal = false ) {
        // setup new filename
        $newFilename = md5 ( microtime ( ) );

        // get database connection
        $db = Database::getInstance ( );

        // create file hash
        $fileHash = md5_file ( $tmpFile );

        // check if the file hash already exists
        $fileExists = false;
        if ( $fileUpload->size > 0 ) {
            $db->query ( "CALL sp_file_load_by_hash ( :file_hash )", array ( "file_hash" => $fileHash ) );
            if ( $db->hasRows ( ) ) {
                $findFile = $db->getRow ( );
                $fileExists = true;
            }
        }

        if ( !$fileExists ) {
            // create the upload folder
            $uploadPathDir = $this->options [ "upload_dir" ] . substr ( $newFilename, 0, 2 );
            @mkdir ( $uploadPathDir );
            @chmod ( $uploadPathDir, 0777 );

            $file_path = $uploadPathDir . "/" . $newFilename;
            clearstatcache ( );
            $rs = false;
            if ( $tmpFile ) {
                if ( $keepOriginal ) {
                    $rs = copy ( $tmpFile, $file_path );
                } else {
                    $rs = rename ( $tmpFile, $file_path );
                }
                if ( $rs ) {
                    @chmod ( $file_path, 0777 );
                }
            }

            if ( !$rs ) {
                $fileUpload->error = "Could not move the file into storage, possibly a permissions issue with the file storage directory.";
            }
            $file_size = filesize ( $file_path );
        }
        else
        {
            $file_size      = $findFile->fil_size;
            $file_path      = $this->options [ "upload_dir" ] . $findFile->fil_path;
        }

        $rs                             = array ( );
        $rs [ "file_size" ]             = $file_size;
        $rs [ "file_path" ]             = $file_path;
        $rs [ "fileUpload" ]            = $fileUpload;
        $rs [ "newFilename" ]           = $newFilename;
        $rs [ "relative_file_path" ]    = substr ( $file_path, 0, strlen ( $this->options [ "upload_dir" ] ) ) == $this->options [ "upload_dir" ] ? substr ( $file_path, strlen ( $this->options [ "upload_dir" ] ) ) : $file_path;
        $rs [ "fileHash" ]              = $fileHash;

        return $rs;
    }

    public function post ( ) {
        $upload = isset ( $_FILES [ $this->options [ "param_name" ] ] ) ?
            $_FILES [ $this->options [ "param_name" ] ] :
            array(
                "tmp_name" => null,
                "name"     => null,
                "size"     => null,
                "type"     => null,
                "error"    => null
            );

        // parse the Content-Disposition header, if available:
        $file_name = $this->getServerVar ( "HTTP_CONTENT_DISPOSITION" ) ? rawurldecode ( preg_replace ( '/(^[^"]+")|("$)/', "", $this->getServerVar ( "HTTP_CONTENT_DISPOSITION" ) ) ) : null;

        // parse the Content-Range header, which has the following form:
        // Content-Range: bytes 0-524287/2000000
        $content_range = $this->getServerVar ( "HTTP_CONTENT_RANGE" ) ?
            preg_split ( '/[^0-9]+/', $this->getServerVar ( "HTTP_CONTENT_RANGE" ) ) : null;
        $size = $content_range ? $content_range [ 3 ] : null;

        $info = array ( );
        if ( is_array ( $upload [ "tmp_name" ] ) ) {
            foreach ( $upload [ "tmp_name" ] as $index => $value ) {
                $info [ ] = $this->handleFileUpload (
                    $upload [ "tmp_name" ] [ $index ], isset ( $_SERVER [ "HTTP_X_FILE_NAME" ] ) ? $_SERVER [ "HTTP_X_FILE_NAME" ] : $upload [ "name" ] [ $index ], isset ( $_SERVER [ "HTTP_X_FILE_SIZE" ] ) ? $_SERVER [ "HTTP_X_FILE_SIZE" ] : $upload [ "size" ] [ $index ], isset ( $_SERVER [ "HTTP_X_FILE_TYPE" ] ) ? $_SERVER [ "HTTP_X_FILE_TYPE" ] : $upload [ "type" ] [ $index ], $upload [ "error" ] [ $index ], $index, $content_range, isset ( $_POST [ "cTracker" ] ) ? $_POST [ "cTracker" ] : null
                );
            }
        } else {
            $info [ ] = $this->handleFileUpload (
                $upload [ "tmp_name" ], isset ( $_SERVER [ "HTTP_X_FILE_NAME" ] ) ? $_SERVER [ "HTTP_X_FILE_NAME" ] : $upload [ "name" ], isset ( $_SERVER [ "HTTP_X_FILE_SIZE" ] ) ? $_SERVER [ "HTTP_X_FILE_SIZE" ] : $upload [ "size" ], isset ( $_SERVER [ "HTTP_X_FILE_TYPE" ] ) ? $_SERVER [ "HTTP_X_FILE_TYPE" ] : $upload [ "type" ], $upload [ "error" ], null, $content_range, isset ( $_POST [ "cTracker" ] ) ? $_POST [ "cTracker" ] : null
            );
        }
        header ( "Vary: Accept" );
        if ( isset ( $_SERVER [ "HTTP_ACCEPT" ] ) && strpos ( $_SERVER [ "HTTP_ACCEPT" ], "application/json" ) !== false ) {
            header ( "Content-type: application/json" );
        } else {
            header ( "Content-type: text/plain");
        }
        echo json_encode ( $info );
    }

    public function hasError ( $uploaded_file, $file, $error = null ) {
		// make sure uploading hasn't been disabled
        if ( $error ) {
            return $error;
        }

        if ( $uploaded_file && file_exists ( $uploaded_file ) ) {
            $file_size = filesize ( $uploaded_file );
        } else {
            $file_size = $_SERVER [ "CONTENT_LENGTH" ];
        }
        if ( $this->options [ "max_file_size" ] && $file_size > $this->options [ "max_file_size" ] || $file->size > $this->options [ "max_file_size" ] ) {
            return "maxFileSize";
        }
        if ( $this->options [ "min_file_size" ] && $file_size < $this->options [ "min_file_size" ] ) {
            return "minFileSize";
        }

        return null;
    }

    private function cleanLeftOverChunks ( ) {
        $localTempStore = self::getLocalTempStorePath ( );

        // loop local temp folder and clear any older than 3 days old
        foreach ( glob ( $localTempStore . "*" ) as $file ) {
            // protect the filename
            if ( filemtime ( $file ) < time ( ) - 60 * 60 * 24 * 3 ) {
                // double check we're in the file store
                if ( substr ( $file, 0, strlen ( $this->options [ "upload_dir" ] ) ) == $this->options [ "upload_dir" ] ) {
                    @unlink ( $file );
                }
            }
        }
    }

    private function getServerVar ( $var ) {
        return isset ( $_SERVER [ $var ] ) ? $_SERVER [ $var ] : "";
    }

    static function generateSuccessHtml ( $fileUpload ) {
        $success_result_html = '';
        $success_result_html .= '<td class="cancel">';
        $success_result_html .= '   <img src="' . THEME_IMAGE_PATH . '/green_tick_small.png" height="16" width="16" alt="success"/>';
        $success_result_html .= '</td>';
        $success_result_html .= '<td class="name">';
        $success_result_html .= $fileUpload->name;
        $success_result_html .= '<div class="sliderContent" style="display: none;">';
        $success_result_html .= '        <!-- popup content -->';
        $success_result_html .= '        <table width="100%">';
        $success_result_html .= '            <tr>';
        $success_result_html .= '                <td class="odd" style="width: 90px; border-top:1px solid #fff;">';
        $success_result_html .= '                    <label>Download Url:</label>';
        $success_result_html .= '                </td>';
        $success_result_html .= '                <td class="odd ltrOverride" style="border-top:1px solid #fff;">';
        $success_result_html .= '                    <a href="' . $fileUpload->url . '" target="_blank">' . $fileUpload->url . '</a>';
        $success_result_html .= '                </td>';
        $success_result_html .= '            </tr>';
        $success_result_html .= '            <tr>';
        $success_result_html .= '                <td class="even">';
        $success_result_html .= '                    <label>HTML Code:</label>';
        $success_result_html .= '                </td>';
        $success_result_html .= '                <td class="even htmlCode ltrOverride" onClick="return false;">';
        $success_result_html .= '                    &lt;a href=&quot;' . $fileUpload->info_url . '&quot; target=&quot;_blank&quot; title=&quot;View on ' . CONFIG_SITE_NAME . '&quot;&gt;View on ' . $fileUpload->name . ' from ' . CONFIG_SITE_NAME . '&lt;/a&gt;';
        $success_result_html .= '                </td>';
        $success_result_html .= '            </tr>';
        $success_result_html .= '            <tr>';
        $success_result_html .= '                <td class="odd">';
        $success_result_html .= '                    <label>Forum Code:</label>';
        $success_result_html .= '                </td>';
        $success_result_html .= '                <td class="odd htmlCode ltrOverride">';
        $success_result_html .= '                    [url]' . $fileUpload->url . '[/url]';
        $success_result_html .= '                </td>';
        $success_result_html .= '            </tr>';
        $success_result_html .= '            <tr>';
        $success_result_html .= '                <td class="even">';
        $success_result_html .= '                    <label>Stats Url:</label>';
        $success_result_html .= '                </td>';
        $success_result_html .= '                <td class="even ltrOverride">';
        $success_result_html .= '                    <a href="' . $fileUpload->stats_url . '" target="_blank">' . $fileUpload->stats_url . '</a>';
        $success_result_html .= '                </td>';
        $success_result_html .= '            </tr>';
        $success_result_html .= '            <tr>';
        $success_result_html .= '                <td class="odd">';
        $success_result_html .= '                    <label>Delete Url:</label>';
        $success_result_html .= '                </td>';
        $success_result_html .= '                <td class="odd ltrOverride">';
        $success_result_html .= '                    <a href="' . $fileUpload->delete_url . '" target="_blank">' . $fileUpload->delete_url . '</a>';
        $success_result_html .= '                </td>';
        $success_result_html .= '            </tr>';
        $success_result_html .= '            <tr>';
        $success_result_html .= '                <td class="even">';
        $success_result_html .= '                    <label>Full Info:</label>';
        $success_result_html .= '                </td>';
        $success_result_html .= '                <td class="even htmlCode ltrOverride">';
        $success_result_html .= '                    <a href="' . $fileUpload->info_url . '" target="_blank" onClick="window.open(\'' . $fileUpload->info_url . '\'); return false;">[click here]</a>';
        $success_result_html .= '                </td>';
        $success_result_html .= '            </tr>';
        $success_result_html .= '        </table>';
        $success_result_html .= '        <input type="hidden" value="' . $fileUpload->short_url . '" name="shortUrlHidden" class="shortUrlHidden"/>';
        $success_result_html .= '    </div>';
        $success_result_html .= '</td>';
        $success_result_html .= '<td class="rightArrow"><img src="' . THEME_IMAGE_PATH . '/blue_right_arrow.png" width="8" height="6" /></td>';
        $success_result_html .= '<td class="url urlOff">';
        $success_result_html .= '    <a href="' . $fileUpload->url . '" target="_blank">' . $fileUpload->url . '</a>';
        $success_result_html .= '    <div class="fileUrls hidden">' . $fileUpload->url . '</div>';
        $success_result_html .= '</td>';

        return $success_result_html;
    }

    static public function getLocalTempStorePath ( ) {
        $tmpDir = $this->options [ "upload_dir" ] . "_tmp/";

        if ( !file_exists ( $tmpDir ) ) {
            @mkdir ( $tmpDir );
        }

        if ( !file_exists ( $tmpDir ) ) {
            self::exitWithError ( Functions::stringReplace ( "Failed creating temp storage folder for chunked uploads. Ensure the parent folder has write permissions: [[[TMP_DIR]]]", array ( "TMP_DIR" => $tmpDir ) ) );
        }

        if ( !is_writable ( $tmpDir ) ) {
            self::exitWithError ( Functions::stringReplace ( "Temp storage folder for uploads is not writable. Ensure it has CHMOD 755 or 777 permissions: [[[TMP_DIR]]]", array ( "TMP_DIR" => $tmpDir ) ) );
        }

        return $tmpDir;
    }

    static function exitWithError ( $errorStr ) {
        $fileUpload                    = new stdClass ( );
        $fileUpload->error             = $errorStr;
        $errorHtml                     = self::generateErrorHtml ( $fileUpload );
        $fileUpload->error_result_html = $errorHtml;
        echo json_encode ( array ( $fileUpload ) );
        exit ( );
    }

    static function generateErrorHtml ( $fileUpload ) {
        $error_result_html = '';
        $error_result_html .= '<td class="cancel">';
        $error_result_html .= '    <img src="' . THEME_IMAGE_PATH .'/red_error_small.png" height="16" width="16" alt="error"/>';
        $error_result_html .= '</td>';

        $error_result_html .= '<td class="name">' . $fileUpload->name . '</td>';

        $error_result_html .= '<td class="error" colspan="2">Error: ';
        $error_result_html .= self::getErrorByCode ( $fileUpload->error );
        $error_result_html .= '</td>';

        return $error_result_html;
    }

    static function getErrorByCode ( $error ) {
        switch ( $error ) {
            case 1:
                return "File exceeds upload_max_filesize (php.ini directive)";
            case 2:
                return "File exceeds MAX_FILE_SIZE (HTML form directive)";
            case 3:
                return "File was only partially uploaded";
            case 4:
                return "No File was uploaded";
            case 5:
                return "Missing a temporary folder";
            case 6:
                return "Failed to write file to disk";
            case 7:
                return "File upload stopped by extension";
            case 'maxFileSize':
                return "File is too big";
            case 'minFileSize':
                return "File is too small";
            default:
                return $error;
        }
    }

    static function createUploadError ( $name, $msg ) {
        $fileUpload                    = new stdClass ( );
        $fileUpload->size              = 0;
        $fileUpload->type              = "";
        $fileUpload->name              = $name;
        $fileUpload->error             = $msg;
        $fileUpload->error_result_html = self::generateErrorHtml ( $fileUpload );

        return json_encode ( array ( $fileUpload ) );
    }
}
