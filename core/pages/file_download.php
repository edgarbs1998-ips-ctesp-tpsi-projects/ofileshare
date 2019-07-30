<?php

// Lad file
$file = null;
if ( isset ( $_GET [ "page" ] ) ) {
	$pageUrl = trim ( $_GET [ "page" ] );

    $shortUrl = current ( explode ( "/", $pageUrl ) );

    $file = File::loadByShortUrl ( $shortUrl );
}

if ( !$file ) {
    Functions::output404 ( );
}

// Get download token
$downloadToken = null;
if ( isset ( $_GET [ File::DOWNLOAD_TOKEN_VAR ] ) ) {
    $downloadToken = $_GET [ File::DOWNLOAD_TOKEN_VAR ];
}

// Check for download managers on original download url
if ( $downloadToken === null && Functions::isDownloadManager ( $_SERVER [ "HTTP_USER_AGENT" ] ) == true ) {
    if ( !isset ( $_SERVER [ "PHP_AUTH_USER" ] ) ) {
        header ( "WWW-Authenticate: Basic realm='Please enter a valid username and password'" );
        Functions::output401 ( );
    }

    $rs = $user->login ( trim ( $_SERVER [ "PHP_AUTH_USER" ] ), trim ( $_SERVER [ "PHP_AUTH_PW" ] ) );
    if ( !$rs ) {
        header ( "WWW-Authenticate: Basic realm='Please enter a valid username and password'" );
        Functions::output401 ( );
    }
    
    $downloadToken = $file->generateDirectDownloadToken ( );
}

// File been removed
if ( $file->fil_trash != null ) {
    $errorMsg = "File has been removed.";
    Functions::redirect ( WEB_ROOT . "/error.html?e=" . urlencode ( $errorMsg ) );
}

// Check file permissions, allow owners and admins
if ( $file->usr_id != null ) {
    if ( $user->isLogged ( ) ) {
	    if ( $file->usr_id != $user->id && $user->usr_ule_level < 20 ) {
    		// if this is a private file
		    if ( $file->fil_fpe_id == 1 ) {
    			$errorMsg = "File is not publicly available.";
			    Functions::redirect ( WEB_ROOT . "/error.html?e=" . urlencode ( $errorMsg ) );
		    }
	    }
    } else {
        // if this is a private file
		if ( $file->fil_fpe_id == 1 ) {
            echo "File is not publicly available.";
            Functions::output401 ( );
	    }
    }
}

// Close database so we don't cause locks during the download
$db->close ( );

// Download file
if ( $downloadToken !== null ) {
    $rs = $file->download ( $downloadToken );
    if ( !$rs ) {
        $errorMsg = "File can not be located, please try again later.";
        if ( $file->errorMsg != null ) {
            $errorMsg = "Error: " . $file->errorMsg;
        }
        Functions::redirect ( WEB_ROOT . "/error.html?e=" . urlencode ( $errorMsg ) );
    }
}

// Page settings
define ( "PAGE_NAME", $file->fil_name );
define ( "PAGE_DESCRIPTION", "Download file" );
define ( "PAGE_KEYWORDS", "download, file, cloud, file, hosting, sharing, upload, storage, site, website" );

// Clear any expired download trackers
DownloadTracker::clearTimedOutDownloads ( );
DownloadTracker::purgeDownloadData ( );

// Generate unique download url
$downloadUrl = $file->generateDirectDownloadUrl ( );
Functions::redirect ( $downloadUrl );
