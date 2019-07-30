<?php

// Allow some time to run (4 hours)
set_time_limit ( 60 * 60 * 4 );

// Setup includes
require_once ( "../includes/core.inc.php" );

// Require user login
$user->requireUser ( WEB_ROOT . "/login.html" );

// Initial headers
header ( "Expires: 0" );
header ( "Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0" );
header ( "Pragma: no-cache" );
http_response_code ( 200 );

// Initial params
$fileId = ( int ) trim ( filter_input ( INPUT_GET, "fileId", FILTER_SANITIZE_NUMBER_INT ) );

// Make sure user owns file
$file = File::loadById ( $fileId );
if ( $file->usr_id != $user->id ) {
	Functions::output404 ( );
}

// Create download token and redirect to file
$directDownloadUrl = $file->generateDirectDownloadUrl ( );
Functions::redirect ( $directDownloadUrl );
