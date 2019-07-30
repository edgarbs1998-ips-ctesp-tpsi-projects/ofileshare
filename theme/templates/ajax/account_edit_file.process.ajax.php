<?php

// Require user login
$user->requireUser ( WEB_ROOT . "/login.html" );

// Setup database
$db = Database::getInstance ( );

// Load file
$fileId = ( int ) $_POST [ "fileId" ];
$file = File::loadById ( $fileId );
if ( !$file ) {
	Functions::output404 ( );
}

if ( $file->usr_id != $user->id ) {
	Functions::output404 ( );
}

// Load folders
$folderListing = FileFolder::loadAllForSelect ( $user->id );

// Handle submission
if ( isset ( $_POST [ "submitme" ] ) ) {
    // Form validation
    $filename       = trim ( filter_input ( INPUT_POST, "filename", FILTER_SANITIZE_STRING ) );
    $folder         = ( int ) trim ( filter_input ( INPUT_POST, "folder", FILTER_SANITIZE_NUMBER_INT ) );
    $filePrivacy    = ( int ) trim ( filter_input ( INPUT_POST, "filePrivacy", FILTER_SANITIZE_NUMBER_INT ) );
    
    if ( !strlen ( $filename ) ) {
        Notification::setError ( "Please enter the filename" );
    } else {
        if ( $file->getFilenameExcExtension ( ) != $filename ) {
            // Check if file name already exists
            $db->query ( "CALL sp_file_duplicate_name ( :user_id, :folder_id, :file_name, :file_id )", array ( "user_id" => $user->id, "folder_id" => $file->fil_fpe_id, "file_name" => $filename . "." . $file->fil_extension, "file_id" => ( int ) $file->fil_id ) );
            if ( $db->getValue ( ) > 0 ) {
                Notification::setError ( "Active file with same name found in the same folder. Please ensure the file name is unique." );
            }
        }
    }

    // Edit file
    if ( !Notification::hasErrors ( ) ) {
        if ( $folder == 0 ) {
            $folder = null;
        }

        try {
            $db->query ( "CALL sp_file_update ( :file_id, :file_name, :folder_id, :file_privacy, :user_id )", array ( "file_id" => $file->fil_id, "file_name" => $filename . "." . $file->fil_extension, "folder_id" => $folder, "file_privacy" => $filePrivacy, "user_id" => $user->id ) );

            Notification::setSuccess ( "File updated." );
        } catch ( APPException $ex ) {
            Notification::setError ( "There was a problem updating the item, please try again later." );
        }
    }
}

// Prepare result
$returnJson = array ( );
if ( Notification::hasErrors ( ) ) {
    $returnJson [ "success" ] = false;
    $returnJson [ "msg" ] = implode ( "<br/>", Notification::getErrors ( ) );
} else {
    $returnJson [ "success" ] = true;
    $returnJson [ "msg" ] = implode ( "<br/>", Notification::getSuccess ( ) );
}

echo json_encode ( $returnJson );
exit ( );
