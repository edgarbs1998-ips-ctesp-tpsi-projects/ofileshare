<?php

// Setup includes
require_once ( "../../includes/core.inc.php" );

// Require login
$user->requireUser ( WEB_ROOT . "/login.html" );

// Prepare result
$result = array ( );
$result [ "msg" ] = "";

// Get file ids
$fileIds = $_POST [ "fileIds" ];

$totalRemoved = 0;
    
// Load files
if ( count ( $fileIds ) ) {
    foreach ( $fileIds as $fileId ) {
        // Load file and process if active and belongs to the currently logged in user
        $file = File::loadById ( $fileId );
        if ( $file && $file->fil_trash == null && $file->usr_id == $user->id ) {
            // Move to trash
            $rs = $file->remove();
            if ( $rs ) {
                $totalRemoved++;
            }
        }
    }
}

$result [ "msg" ] = "Removed " . $totalRemoved . " file" . ( $totalRemoved != 1 ? "s" : "" ) . ".";

echo json_encode($result);
exit ( );
