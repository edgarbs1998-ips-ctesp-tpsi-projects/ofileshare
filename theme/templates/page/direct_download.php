<?php

// Allow some time to run (4 hours)
set_time_limit ( 60 * 60 * 4 );

// Initial headers
header ( "Expires: 0" );
header ( "Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0" );
header ( "Pragma: no-cache" );
http_response_code ( 200 );

// Initial params
$fileId 	= ( int ) trim ( filter_input ( INPUT_GET, "fileId", FILTER_SANITIZE_NUMBER_INT ) );
$fileHash 	= ( int ) trim ( filter_input ( INPUT_GET, "fileHash" ) );

// Load file
$file = File::loadById ( $fileId );
if ( !$file ) {
	Functions::output404 ( );
}

// Check file hash
if ( $file->getFileHash ( ) != $fileHash ) {
	Functions::output404 ( );
}

// Public status
$isPublic = 0;
if ( $file->fil_fpe_id == 2 ) {
    $isPublic = 1;
}

// Check if user is allowed to download the file
if ( !$isPublic && $user->id != $file->usr_id ) {
	http_response_code ( 401 );
	exit ( );
}

// Create download token and redirect to file
$directDownloadUrl = $file->generateDirectDownloadUrl ( );
Functions::redirect ( $directDownloadUrl );
