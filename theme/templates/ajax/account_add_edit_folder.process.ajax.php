<?php

// Require user login
$user->requireUser ( WEB_ROOT . "/login.html" );

// Setup database
$db = Database::getInstance ( );

// Load folders
$folderListing = FileFolder::loadAllForSelect ( $user->id );

// Handle submission
if ( isset ( $_POST [ "submitme" ] ) ) {
    // Form validation
    $folderName     = trim ( filter_input ( INPUT_POST, "folderName", FILTER_SANITIZE_STRING ) );
    $parentId       = ( int ) trim ( filter_input ( INPUT_POST, "parentId", FILTER_SANITIZE_NUMBER_INT ) );

    $editFolderId = null;
    if ( isset ( $_POST [ "editFolderId" ] ) ) {
        $editFolderId   = ( int ) trim ( filter_input ( INPUT_POST, "editFolderId", FILTER_SANITIZE_NUMBER_INT ) );
    }

    if ( !strlen ( $folderName ) ) {
        Notification::setError ( "Please enter the folder name" );
    } else {
        // Check for existing folder name
        $db->query ( "CALL sp_folder_duplicate_name ( :user_id, :folder_parent, :folder_name, :folder_id )", array ( "user_id" => $user->id, "folder_parent" => ( $parentId == -1 ? null : $parentId ), "folder_name" => $folderName, "folder_id" => $editFolderId ) );
        if ( $db->hasRows ( ) ) {
            Notification::setError ( "You already have an folder with that name, please use another name" );
        }
    }

    $returnFolderId = ( int ) $parentId;

    // Add/Update folder
    if ( !Notification::hasErrors ( ) ) {
        if ( !isset ( $folderListing [ $parentId ] ) ) {
            $parentId = null;
        }

        $db = Database::getInstance ( );

        // Update folder
        if ( $editFolderId !== null ) {
            try {
                $db->query ( "CALL sp_folder_update ( :user_id, :folder_id, :folder_parent, :folder_name )", array ( "user_id" => $user->id, "folder_id" => $editFolderId, "folder_parent" => $parentId, "folder_name" => $folderName ) );
                Notification::setSuccess ( "Folder updated." );
            } catch ( APPException $ex ) {
                Notification::setError ( "There was a problem updating the folder, please try again later." );
            }
        }
        // Add folder
        else {
            $db->query ( "CALL sp_folder_add ( :user_id, :folder_parent, :folder_name )", array ( "user_id" => $user->id, "folder_parent" => $parentId, "folder_name" => $folderName ) );
            if ( $db->hasRows ( ) ) {
                $returnFolderId = $db->insertId ( );
                Notification::setSuccess ( "Folder created." );
            } else {
                Notification::setError ( "There was a problem adding the folder, please try again later." );
            }
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
$returnJson [ "folder_id" ] = $returnFolderId;

// Rebuild folder html
$folderArr = array ( );
if ( $user->isLogged ( ) ) {
    $folderArr = FileFolder::loadAllForSelect ( $user->id );
}
$returnJson [ "folder_listing_html" ] = '<select id="upload_folder_id" name="upload_folder_id" class="form-control" ' . ( !$user->isLogged ( ) ? 'disabled' : '' ) . '>';
$returnJson [ "folder_listing_html" ] .= '	<option value="">' . ( !$user->isLogged ( ) ? "- login to enable -" : "- default -" ) . '</option>';
if ( count ( $folderArr ) ) {
    foreach ( $folderArr as $id => $folderLabel ) {
        $returnJson [ "folder_listing_html" ] .= '<option value="' . $id . '"';
        if ( $returnFolderId == $id ) {
            $returnJson [ "folder_listing_html" ] .= ' selected';
        }
        $returnJson [ "folder_listing_html" ] .= '>' . Validate::prepareOutput ( $folderLabel ) . '</option>';
    }
}
$returnJson [ "folder_listing_html" ] .= '</select>';

echo json_encode ( $returnJson );
exit ( );
