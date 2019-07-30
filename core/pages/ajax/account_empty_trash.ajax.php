<?php

// Setup includes
require_once ( "../../includes/core.inc.php" );

// Require login
$user->requireUser ( WEB_ROOT . "/login.html" );

// Prepare result
$result = array ( );
$result [ "error" ] = false;
$result [ "msg" ] = "";

// Load all files
$db->query ( "SELECT fil_id FROM file INNER JOIN folder ON fol_id = fil_fol_id WHERE fol_usr_id = :user_id AND fil_trash IS NOT NULL", array ( "user_id" => $user->id ) );
$rows = $db->getRows( );
if ( $rows ) {
    foreach ( $rows AS $row ) {
        try {
            $db->query ( "CALL sp_file_delete ( :file_id )", array ( "file_id" => $row->fil_id ) );
        } catch ( APPException $ex ) {
            $result [ "error" ] = true;
        }
    }
}

if ( $result [ "error" ] ) {
    $result [ "msg" ] = "Some files could not be removed.";
} else {
    $result [ "msg" ] = "Trash emptied.";
}

echo json_encode ( $result );
exit ( );
