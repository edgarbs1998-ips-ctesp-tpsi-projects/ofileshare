<?php

class Functions {
    static function checkMaintenanceMode ( ) {
        //$auth = Auth::getInstance ( );

        if ( CONFIG_MAINTENANCE_MODE /*&& $auth->level_id <= 2*/ ) {
            include_once ( CORE_ROOT . "/pages/maintenance.php" );
            exit ( );
        }
    }

    static function redirect ( $url = null ) {
        if ( is_null ( $url ) ) {
            $url = $_SERVER [ "PHP_SELF" ];
        }

        if ( !headers_sent ( ) ) {
            header ( "Location: " . $url );
            exit ( );
        }

        echo    "<script type='text/javascript'>
                    window.location.href='" . $url . "';
                </script>
                <noscript>
                    <meta http-equiv='refresh' content='0;url=" . $url . "' />
                </noscript>";
        exit ( );
    }

    static function httpPost ( $url, $data ) {
        if ( function_exists ( "curl_init" ) ) {
            $ch = curl_init ( );
            curl_setopt ( $ch, CURLOPT_URL, $url );
            curl_setopt ( $ch, CURLOPT_POST, true );
            curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
            curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 5 );

            $response = curl_exec ( $ch );
            curl_close ( $ch );

            if ( strlen ( $response ) ) {
                return $response;
            }
        }

        if ( ini_get ( "allow_url_fopen" ) ) {
            $queryData = http_build_query ( $data );

            $options = array (
                "http" => array (
                    "method" => "POST",
                    "header" => "Content-type: application/x-www-form-urlencoded",
                    "content" => $queryData,
                )
            );

            $context = stream_context_create ( $options );

            $response = file_get_contents ( $url, false, $context );

            if ( strlen ( $response ) ) {
                return $response;
            }
        }

        // Cannot open url, either install curl-php or set allow_url_fopen = true in php.ini
        return false;
    }

    static function getUserIpAddress ( ) {
        // Cloudflare
        if ( isset ( $_SERVER [ "HTTP_CF_CONNECTING_IP" ] ) && strlen ( $_SERVER [ "HTTP_CF_CONNECTING_IP" ] ) ) {
            return $_SERVER [ "HTTP_CF_CONNECTING_IP" ];
        }

        // Nginx proxy to apache users
        if ( isset ( $_SERVER [ "HTTP_X_REAL_IP" ] ) &&  strlen ( $_SERVER [ "HTTP_X_REAL_IP" ] ) ) {
            return $_SERVER [ "HTTP_X_REAL_IP" ];
        }

        if ( isset ( $_SERVER [ "REMOTE_ADDR" ] ) && strlen ( $_SERVER [ "REMOTE_ADDR" ] ) ) {
            return $_SERVER [ "REMOTE_ADDR" ];
        }

        return null;
    }

    static function stringReplace ( $string, $replacements ) {
        if ( count ( $replacements ) ) {
            foreach ( $replacements AS $key => $value ) {
                $string = str_replace ( "[[[" . strtoupper ( $key ) . "]]]", $value, $string );
            }
        }

        return $string;
    }

    static function sendEmail ( $to, $subject, $message, $fromEmail = null, $fromName = null, $replyToEmail = null, $replyToName = "" ) {
        if ( !is_array ( $to ) ) {
            $to = array ( $to );
        }

        if ( $fromEmail == null ) {
            $fromEmail = CONFIG_EMAIL_FROM_DEFAULT;
        }

        if ( $fromName == null ) {
            $fromName = CONFIG_SITE_NAME;
        }

        if ( $replyToEmail == null ) {
            $replyToEmail = $fromEmail;
            $replyToName = $fromName;
        }

        $templateHeader = file_get_contents ( CORE_ROOT . "/pages/emailTemplateHeader.inc.php" );
        $templateFooter = file_get_contents ( CORE_ROOT . "/pages/emailTemplateFooter.inc.php" );

        $templateReplacements = array(
            "WEB_ROOT"          => WEB_ROOT,
            "SITE_NAME"         => CONFIG_SITE_NAME,
            "DATE_TIME_NOW"     => date ( "d/m/Y H:i:s" ),
        );

        $templateHeader = Functions::stringReplace ( $templateHeader, $templateReplacements );
        $templateFooter = Functions::stringReplace ( $templateFooter, $templateReplacements );

        $body = $templateHeader . $message . $templateFooter;

        $mail = new PHPMailer ( true );

        try {
            $mail->isMail ( );

            if ( CONFIG_EMAIL_METHOD == "smtp" && strlen ( CONFIG_EMAIL_SMTP_HOST ) ) {
                $mail->isSMTP ( );
                $mail->SMTPDebug = ( DEBUG_MODE ) ? 3 : 0;
                $mail->Debugoutput = "html";
                $mail->Host = CONFIG_EMAIL_SMTP_HOST;
                $mail->Port = CONFIG_EMAIL_SMTP_PORT;
                $mail->SMTPSecure = CONFIG_EMAIL_SMTP_SECURE;
                $mail->SMTPAuth = ( strlen ( CONFIG_EMAIL_SMTP_USERNAME ) ) ? true : false;
                $mail->Username = CONFIG_EMAIL_SMTP_USERNAME;
                $mail->Password = CONFIG_EMAIL_SMTP_PASSWORD;
            }

            $mail->setFrom ( $fromEmail, $fromName );
            $mail->addReplyTo ( $replyToEmail, $replyToName );
            foreach ( $to AS $address ) {
                $mail->addAddress ( $address );
            }

            $mail->Subject = $subject;
            $mail->msgHTML ( $body, SITE_THEME_DIRECTORY_ROOT );

            $mail->send ( );
        } catch ( phpmailerException $ex ) {
            throw new APPException ( $ex );
        } catch ( Exception $ex ) {
            throw new APPException ( $ex );
        }

        return true;
    }

    static function formatSize ( $bytes, $return = "both" ) {
        $units = array ( "B", "kB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB" );
        $factor = floor ( ( strlen ( $bytes ) - 1 ) / 3 );

        switch ( $return ) {
            case "size":
                return number_format ( $bytes / pow ( 1024, $factor ), 2 );
            case "ext":
                return @$units [ $factor ];
            default:
                return number_format ( $bytes / pow ( 1024, $factor ), 2 ) . " " . @$units [ $factor ];
        }
    }

    static function convertDateToTimestamp ( $date, $format = "Y-m-d H:i:s" ) {
        if ( !Validate::validDate ( $date, $format ) ) {
            return false;
        }

        $d = DateTime::createFromFormat ( $format, $date );

        return $d->getTimestamp ( );
    }

    static function output401 ( ) {
        http_response_code ( 401 );
        exit ( );
    }

    static function output404 ( ) {
        http_response_code ( 404 );
        exit ( );
    }

    static function currentBrowserIsIE ( ) {
        if ( isset ( $_SERVER [ "HTTP_USER_AGENT" ] ) && strpos ( $_SERVER [ "HTTP_USER_AGENT" ], "MSIE" ) !== false ) {
			return true;
		}
		
		return false;
	}

    static function checkBrowserSupportsMultipleUploads ( ) {
		if ( strpos ( $_SERVER [ "HTTP_USER_AGENT" ], "iPhone" ) || strpos ( $_SERVER [ "HTTP_USER_AGENT" ], "iPad") || strpos ( $_SERVER [ "HTTP_USER_AGENT" ], "iPod" ) !== false ) {
			if ( strpos ( $_SERVER [ "HTTP_USER_AGENT" ], "OS 8_0" ) !== false || strpos ( $_SERVER [ "HTTP_USER_AGENT" ], "OS 7_0" ) !== false ) {
				return false;
			}
		}
		return true;
	}

    static function convertToBytes ( $value ) {
        $value = trim ( $value );
        $lastValue = strtoupper ( $value [ strlen ( $value ) - 1 ] );
        $value = substr ( $value, 0, -1 );
        switch ( $lastValue ) {
            case "G":
                $value *= pow ( 1024, 3 );
            case "M":
                $value *= pow ( 1024, 2 );
            case "K":
                $value *= 1024;
        }

        return $value;
    }

    static function getPHPMaxUpload ( ) {
        $postMaxSize = self::convertToBytes ( ini_get ( "post_max_size" ) );
        $uploadMaxFilesize = self::convertToBytes ( ini_get ( "upload_max_filesize" ) );
        if ( $postMaxSize > $uploadMaxFilesize ) {
            return $uploadMaxFilesize;
        }

        return $postMaxSize;
    }

    static function flushOutput ( ) {
        ob_flush ( );
        flush ( );
    }

    static function isDownloadManager ( $userAgent ) {
		$userAgent = trim ( $userAgent );
		if ( strlen ( $userAgent ) == 0 ) {
			return false;
		}

		$dlUserAgents  = "Charon|DAP |DA |DC-Sakura|Download Demon|Download Druid|Download Express|";
		$dlUserAgents .= "Download Master|Download Ninja|Download Wonder|DownloadDirect|FDM |FDM/|FileHound|";
		$dlUserAgents .= "FlashGet|FreshDownload|Gamespy_Arcade|GetRight|GetRightPro|Go!Zilla|HiDownload|";
		$dlUserAgents .= "HTTPResume|ICOO Loader|iGetter|Iria/|JetCar|JDownloader|Kontiki Client|LeechGet|";
		$dlUserAgents .= "LightningDownload|Mass Downloader|MetaProducts Download Express|MyGetRight|NetAnts|";
		$dlUserAgents .= "NetPumper|Nitro Downloader|Octopus|PuxaRapido|RealDownload|SmartDownload|SpeedDownload|";
		$dlUserAgents .= "SQ Webscanner|Stamina|Star Downloader|StarDownloader|WebReaper|WebStripper|";
		$dlUserAgents .= "WinGet|WWWOFFLE|wxDownload Fast";
		$dlUserAgentsArr = explode ( "|", $dlUserAgents );
		foreach ( $dlUserAgentsArr as $dlUserAgent ) {
			if ( substr ( $userAgent, 0, strlen ( $dlUserAgent ) ) == $dlUserAgent ) {
				return true;
			}
		}

		return false;
	}
}
