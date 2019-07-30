<?php

class File {
    public $errorMsg = null;

    const DOWNLOAD_TOKEN_VAR = "download_token";

    public function download ( $downloadToken = null, $forceDownload = true, $fileTransfer = true ) {
        // Check download token
        if ( $downloadToken == null ) {
            return false;
        }

        // Get database
        $db = Database::getInstance ( );

        // Params
        $userLevelId = 0;
        $fileOwnerUserId = 0;
        $speed = 0; // Unlimited
        $fileContent = "";

        // Check token
        $db->query ( "CALL sp_download_token_load ( :token, :file_id )", array ( "token" => $downloadToken, "file_id" => $this->fil_id ) );
        $tokenData = $db->getRow( );
        if ( !$tokenData ) {
            return false;
        }

        // Get user level
        if( $tokenData->dto_usr_id > 0 ) {
            $fileOwnerUserId = ( int ) $tokenData->dto_usr_id;
            $userLevelId = ( int ) User::getLevelBYUserId ( $fileOwnerUserId );
        }

        // Clear any expired download trackers
        DownloadTracker::clearTimedOutDownloads ( );
        DownloadTracker::purgeDownloadData ( );

        // PHP script timeout for long downloads (2 days)
        if ( false === strpos ( ini_get ( "disable_functions" ), "set_time_limit" ) ) {
            @set_time_limit ( 60 * 60 * 24 * 2 );
        }

        // Get file path
        $storageLocation = DOC_ROOT . "/files/";
        $fullPath = $this->getFullFilePath ( $storageLocation );

        // Handle where to start in the download, support for resumed downloads
        $seekStart = 0;
        $seekEnd = $this->fil_size - 1;
        if ( isset ( $_SERVER [ "HTTP_RANGE" ] ) || isset ( $HTTP_SERVER_VARS [ "HTTP_RANGE" ] ) ) {
            if ( isset ( $HTTP_SERVER_VARS [ "HTTP_RANGE" ] ) ) {
                $seekRange = substr ( $HTTP_SERVER_VARS [ "HTTP_RANGE" ], strlen ( "bytes=" ) );
            } else {
                $seekRange = substr ( $_SERVER [ "HTTP_RANGE" ], strlen ( "bytes=" ) );
            }

            $range = explode ( "-", $seekRange );
            if ( ( int ) $range [ 0 ] > 0 ) {
                $seekStart = intval ( $range [ 0 ] );
            }

            if ( ( int ) $range [ 1 ] > 0 ) {
                $seekEnd = intval ( $range [ 1 ] );
            }
        }

        if ( $forceDownload ) {
            // Output some headers
            header ( "Expires: 0" );
            
            header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
            header( "Content-type: " . $this->fil_type );
            header( "Pragma: public" );
            
            if ( $fileTransfer ) {
                header ( "Content-Disposition: attachment; filename=\"" . str_replace ( "\"", "", $this->fil_name ) . "\"" );
                header ( "Content-Description: File Transfer" );
            }

            header ( "Accept-Ranges: bytes" );
            
            // Allow for requests from iso devices for the first byte
            if ( $seekStart > 0 || $seekEnd == 1 ) {
                header ( "HTTP/1.0 206 Partial Content" );
                header ( "Status: 206 Partial Content" );
                header ( "Content-Length: " . ( $seekEnd - $seekStart + 1 ) );
                header ( "Content-Range: bytes " . $seekStart . "-" . $seekEnd . "/" . $this->fil_size );
            } else {
                header ( "Content-Length: " . $this->fil_size );
                header ( "Content-Range: bytes " . $seekStart . "-" . $seekEnd . "/" . $this->fil_size );
            }
        }

        // Track downloads
        $downloadTracker = new DownloadTracker ( $this );
        $downloadTracker->create ( $seekStart, $seekEnd );

        if ( function_exists ( "apache_setenv" ) ){
            // Disable gzip HTTP compression so it would not alter the transfer rate
            apache_setenv ( "no-gzip", "1" );
        }

        // Clear old tokens
        self::purgeDownloadTokens ( );

        // Open file
        $timeTracker = time ( );
        $length = 0;
        
        if ( !file_exists ( $fullPath ) ) {
            $this->errorMsg = "Could not open file for reading.";
            return false;
        }
        $handle = @fopen ( $fullPath, "r" );
        if ( !$handle ) {
            $this->errorMsg = "Could not open file for reading.";
            return false;
        }

        // Move to starting position
        fseek ( $handle, $seekStart );
        while ( ( $buffer = fgets ( $handle, 4096 ) ) !== false ) {
            if ( $forceDownload ) {
                echo $buffer;
                Functions::flushOutput ( );
                if ( $speed > 0 ) {
                    $usleep = strlen ( $buffer ) / $speed;
                    if ( $usleep > 0 ) {
                        usleep ( $usleep * 1000000 );
                    }
                }
            } else {
                $fileContent .= $buffer;
            }

            $length = $length + strlen ( $buffer );

            // Update download status every DOWNLOAD_TRACKER_UPDATE_FREQUENCY seconds
            if ( ( $timeTracker + DOWNLOAD_TRACKER_UPDATE_FREQUENCY ) < time ( ) ) {
                $timeTracker = time ( );
                $downloadTracker->update();
            }
        }
        fclose ( $handle );

        // Close download
        $downloadTracker->finish ( );

        // Return file content
        if ( !$forceDownload ) {
            return $fileContent;
        }

        exit ( );
    }

    function getIconPreviewImageUrl ( $size, $css = false ) {
        $iconFilePath = "/file_icons/" . $size . "px/" . $this->fil_extension . ".png";
        $iconUrl = THEME_IMAGE_PATH . $iconFilePath;
        if ( $css == true ) {
            $iconUrl = "sprite_icon_" . str_replace ( array ( "+" ), "", $this->fil_extension );
        }
        if ( !file_exists ( SITE_THEME_DIRECTORY_ROOT . "/images" . $iconFilePath ) ) {
            $iconUrl = THEME_IMAGE_PATH . "/file_icons/" . $size . "px/_page.png";
            if ( $css == true ) {
                $iconUrl = 'sprite_icon__page';
            }
        }

        return $iconUrl;
    }

    function getIconPreviewImageUrlMedium ( ) {
        return $this->getIconPreviewImageUrl ( 24 );
    }

    public function getHtmlLinkCode ( ) {
        return "&lt;a href=&quot;" . $this->getFullShortUrl ( ) . "&quot; target=&quot;_blank&quot; title=&quot;View on " . CONFIG_SITE_NAME . "&quot;&gt;View on " . Validate::prepareOutput ( Validate::prepareOutput ( $this->fil_name ) ) . " from " . CONFIG_SITE_NAME . "&lt;/a&gt;";
    }

    public function getForumLinkCode ( ) {
        return "[url]" . Validate::prepareOutput ( $this->getFullShortUrl ( ) ) . "[/url]";
    }

    public function getStatisticsUrl ( $returnAccount = false ) {
        return $this->getShortUrlPath ( ) . "~s" . ( $returnAccount ? "&returnAccount=1" : "" );
    }

    public function getFullShortUrl ( ) {
        return $this->getFullLongUrl ( );
    }

    public function getShortUrlPath ( ) {
        return "http://" . CONFIG_SITE_URL . "/" . $this->fil_shorturl;
    }

    public function getFullLongUrl ( ) {
        return $this->getShortUrlPath ( ) . "/" . $this->getSafeFilenameForUrl ( );
    }

    public function getSafeFilenameForUrl ( ) {
        return str_replace ( array (" ", "\"", "'", ";", "#", "%" ), "_", strip_tags ( $this->fil_name ) );
    }

    public function getDeleteUrl ( $returnAccount = false ) {
        return $this->getShortUrlPath ( ) . "~d?" . $this->fil_delete_hash . ( $returnAccount ? "&returnAccount=1" : "" );
    }

    public function getInfoUrl ( $returnAccount = false ) {
        return $this->getShortUrlPath() . "~i?" . $this->fil_delete_hash . ( $returnAccount ? "&returnAccount=1" : "" );
    }

    static function loadById ( $fileId ) {
        $db = Database::getInstance ( );
        $db->query ( "CALL sp_file_load_by_id ( :file_id )", array ( "file_id" => $fileId ) );
        if ( !$db->hasRows ( ) ) {
            return false;
        }

        $row = $db->getRow ( );
        $file = new File ( );
        foreach ( $row AS $key => $value ) {
            $file->$key = $value;
        }

        return $file;
    }

    static function loadByShortUrl ( $shortUrl ) {
        $db = Database::getInstance ( );
        $db->query ( "CALL sp_file_load_by_shorturl ( :shorturl )", array ( "shorturl" => $shortUrl ) );
        if ( !$db->hasRows ( ) ) {
            return false;
        }

        $row = $db->getRow ( );
        $file = new File ( );
        foreach ( $row AS $key => $value ) {
            $file->$key = $value;
        }

        return $file;
    }

    public function getFilenameExcExtension ( ) {
        $filename = $this->fil_name;
        $extWithDot = "." . $this->fil_extension;
        if ( substr ( $filename, ( strlen ( $filename ) - strlen ( $extWithDot ) ), strlen ( $extWithDot ) ) == $extWithDot ) {
            $filename = substr ( $filename, 0, ( strlen ( $filename ) - strlen ( $extWithDot ) ) );
        }

        return $filename;
    }

    public function remove ( ) {
        $db = Database::getInstance ( );

        $rs = $this->removeFile ( );
        if ( $rs ) {
            $db->query ( "CALL sp_file_trash ( :file_id )", array ( "file_id" => $this->fil_id ) );
            if ( $db->hasRows ( ) ) {
                return true;
            }
        }

        return false;
    }

    public function getFullFilePath ( $prePath = "" ) {
        if ( substr ( $prePath, strlen ( $prePath ) - 1, 1) == "/" ) {
            $prePath = substr ( $prePath, 0, strlen ( $prePath ) - 1 );
        }

        return $prePath . "/" . $this->fil_path;
    }

    public function removeFile ( ) {
        $storageLocation = DOC_ROOT . "/files/";

        $filePath = $this->getFullFilePath ( $storageLocation );
        
        if ( file_exists ( $filePath ) ) {
            $finalPath = $storageLocation . "_deleted/" . $this->fil_path;
            if ( !file_exists ( dirname ( $finalPath ) ) ) {
                @mkdir ( dirname ( $finalPath ), 0755, true );
            }
            
            @rename ( $filePath, $finalPath );
        }

        return true;
    }

    public function getFolderData ( ) {
        $folder = FileFolder::loadById ( ( int ) $this->fil_fol_id );
        if ( !$folder ) {
            return false;
        }

        return $folder;
    }

    public function getOwnerUsername ( ) {
        return User::getUsernameById ( $this->usr_id );
    }

    static function getTotalActiveFilesByUser ( $userId ) {
        $db = Database::getInstance();
        $db->query ( "SELECT COUNT(*) AS total FROM file INNER JOIN folder ON fol_id = fil_fol_id WHERE fol_usr_id = :user_id AND fil_trash IS NULL", array ( "user_id" => ( int ) $userId ) );
        return $db->getValue( );
    }

    static function getTotalTrashedFilesByUser ( $userId ) {
        $db = Database::getInstance();
        $db->query ( "SELECT COUNT(*) AS total FROM file INNER JOIN folder ON fol_id = fil_fol_id WHERE fol_usr_id = :user_id AND fil_trash IS NOT NULL", array ( "user_id" => ( int ) $userId ) );
        return $db->getValue( );
    }

    public function getFileHash ( ) {
        $fileHash = $this->fil_unique_hash;
        if ( strlen ( $fileHash ) == 0 ) {
            $fileHash = self::createUniqueFileHash ( $this->fil_id );
        }

        return $fileHash;
    }

    public function generateDirectDownloadUrl ( ) {
        $db = Database::getInstance ( );

        $downloadToken = $this->generateDirectDownloadToken ( );
        if ( !$downloadToken ) {
            $errorMsg = "Failed generating direct download link, please try again later.";
            return WEB_ROOT . "/error.html?e=" . urlencode ( $errorMsg );
        }

        return $this->getFullShortUrl ( ) . "?" . self::DOWNLOAD_TOKEN_VAR . "=" . $downloadToken;
    }

    public function generateDirectDownloadToken ( ) {
        $db = Database::getInstance ( );

        $user = User::getInstance ( );

        do {
            $downloadToken = hash ( "sha256", $this->fil_id . microtime ( ) . rand ( ) );
            $db->query ( "SELECT dto_id FROM download_token WHERE dto_fil_id = :file_id AND dto_token = :token", array ( "file_id" => $this->fil_id, "token" => $downloadToken ) );
        } while ( $db->hasRows ( ) );

        $userId = "";
        if ( $user->isLogged ( ) ) {
            $userId = $user->id;
        }

        try {
            $db->query ( "CALL sp_download_token_add (  :file_id,
                                                        :user_id,
                                                        :user_ip,
                                                        :token,
                                                        :expire_date )",
                        array (
                            "file_id"       => $this->fil_id,
                            "user_id"       => $userId,
                            "user_ip"       => Functions::getUserIpAddress ( ),
                            "token"         => $downloadToken,
                            "expire_date"   => date ( "Y-m-d H:i:s", time ( ) + ( 60 * 60 * 24 /* 1 Day */) ),
                        )
            );
        } catch ( APPException $ex ) {
            return false;
        }

        return $downloadToken;
    }

    static function purgeDownloadTokens ( ) {
        $db = Database::getInstance ( );

        $db->query ( "DELETE FROM download_token WHERE dto_expire_date < NOW()" );
    }

    static function createUniqueFileHash ($fileId ) {
        $db = Database::getInstance ( );

        $uniqueHash = self::createUniqueFileHashString ( );

        $db->query ( "CALL sp_file_update_unique_hash ( :file_id, :unique_hash )", array ( "file_id" => ( int ) $fileId, "unique_hash" => $uniqueHash ) );

        return $uniqueHash;
    }

    static function createUniqueFileHashString ( ) {
        $db = Database::getInstance ( );

        do {
            $uniqueHash = md5 ( microtime ( ) . rand ( ) ) . md5 ( microtime ( ) . rand ( ) );
            $db->query ( "CALL sp_file_load_by_unique_hash ( :unique_hash )", array ( "unique_hash" => $uniqueHash ) );
        } while ( $db->hasRows ( ) );

        return $uniqueHash;
    }

    static function updateShortUrl ( $fileId, $shortUrl = "TEMP" ) {
        $db = Database::getInstance ( );
        $db->query( "CALL sp_file_update_shorturl ( :file_id, :file_shorturl )", array ( "file_id" => $fileId, "file_shorturl" => $shortUrl ) );
    }

    static function createShortUrlPart ( $value ) {
        return substr ( md5 ( $value . microtime ( ) ), 0, 16 );
    }

    static function getTagArrFromString ( $str = "" ) {
        $str = strtolower ( $str );

        // Remove invalid characters
        $str = str_replace ( array ( "_", "-", ".", ",", "(", ")" ), " ", $str );

        // Remove double spaces
        $str = preg_replace ( '/\s+/', " ", $str );

        $tags = explode ( " ", $str );

        return $tags;
    }

    static function hydrate ( $folderDataArr ) {
        $folderObj = new File ( );
        foreach ( $folderDataArr AS $key => $value ) {
            $folderObj->$key = $value;
        }

        return $folderObj;
    }
}
