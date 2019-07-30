<?php

// Setup includes
require_once ( "../../includes/core.inc.php" );

// Initial headers
header ( "Expires: 0" );
header ( "Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0" );
header ( "Pragma: no-cache" );
http_response_code ( 200 );

// double check user is logged in if required
if ( !$user->isLogged ( ) ) {
    echo Uploader::createUploadError( "Unavailable.", "User is not logged in." );
    exit ( );
}

// check the user hasn't reached the maximum storage on their account
$userAvailableFileStorage = User::getAvailableFileStorage ( $user->id );
if ( $userAvailableFileStorage !== null && $userAvailableFileStorage <= 0 ) {
    echo Uploader::createUploadError( "File upload space full..", "Upload storage full, please delete some active files and try again." );
    exit ( );
}

if ( $_SERVER [ "REQUEST_METHOD" ] == "POST" ) {
    $uploadChunks = 104857600;
    if ( isset ( $_POST [ "maxChunkSize" ] ) ) {
        $uploadChunks = ( int ) trim ( $_POST [ "maxChunkSize" ] );
        if ( $uploadChunks == 0 ) {
            $uploadChunks = 104857600;
        }
    }
    if ( Functions::getPHPMaxUpload ( ) < $uploadChunks ) {
        echo Uploader::createUploadError( "PHP Upload Limit.", Functions::replaceString ( "Your PHP limits on [[[SERVER_NAME]]] need to be set to at least [[[MAX_SIZE]]] to allow larger files to be uploaded (currently [[[CURRENT_LIMIT]]]). Contact your host to set.", array ( "MAX_SIZE" => Functions::formatSize ( $uploadChunks ), "SERVER_NAME" => CONFIG_SITE_HOST_URL, "CURRENT_LIMIT" => Functions::formatSize ( Functions::getPHPMaxUpload ( ) ) ) ) );
        exit ( );
    }

    header ( "Content-Disposition: inline; filename='files.json'" );
    $uploader = new Uploader (
            array (
                "max_chunk_size" => ( int ) $_POST [ "maxChunkSize" ],
                "folder_id" => ( int ) $_POST [ "folderId" ],
            )
    );
    $uploader->post ( );

    exit ( );
}

http_response_code ( 405 );
exit ( );
