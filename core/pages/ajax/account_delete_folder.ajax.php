<?php

// Setup includes
require_once ( "../../includes/core.inc.php" );

// Require login
$user->requireUser ( WEB_ROOT . "/login.html" );

// Prepare result
$result = array ( );
$result [ "error" ] = false;
$result [ "msg" ] = "Error removing folder.";
$result [ "parent_folder" ] = "";

$folderId = ( int ) $_GET [ "folderId" ];

$fileFolder = FileFolder::loadById ( $folderId );
if ( $fileFolder ) {
    if ( $fileFolder->fol_usr_id == $user->id) {
        $fileFolder->remove ( );
            
        $result [ "error" ] = false;
        $result [ "msg" ] = "Folder deleted.";
        $result [ "parent_folder" ] = $fileFolder->fol_parent;
    }
}

echo json_encode ( $result );
exit ( );
