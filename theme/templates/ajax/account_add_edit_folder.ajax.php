<?php

// Require user login
$user->requireUser ( WEB_ROOT . "/login.html" );

// Setup database
$db = Database::getInstance ( );

// Load folders
$folderListing = FileFolder::loadAllForSelect ( $user->id );

// Setup parent folder
$parentId = "-1";
if ( isset ( $_POST [ "parentId" ] ) ) {
    $parentId = ( int ) $_POST [ "parentId" ];
}

// Defaults params
$editFolderId = null;
if( $_POST [ "editFolderId" ] ) {
    $fileFolder = FileFolder::loadById ( ( int ) $_POST [ "editFolderId" ] );
    if ( $fileFolder ) {
        $pageUrl = $fileFolder->getFolderUrl ( );
        $editFolderId = $fileFolder->fol_id;
        $folderName = $fileFolder->fol_name;
        $parentId = $fileFolder->fol_parent;
    }
}
?>

<form action="<?php echo WEB_ROOT; ?>/ajax/account_add_edit_folder.process.ajax.php" autocomplete="off" onkeypress="return event.keyCode != 13;">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title"><?php echo $editFolderId == null ? "Add Folder" : ( "Edit Existing Folder  (" . Validate::prepareOutput ( $folderName ) . ")"); ?></h4>
    </div>

    <div class="modal-body">
        <div class="row">

            <div class="col-md-3">
<?php
$icon = "edit";
if ( $editFolderId == null ) {
    $icon = "plus";
}
?>
                <div class="modal-icon-left"><img src="<?php echo THEME_IMAGE_PATH; ?>/modal_icons/folder_yellow_<?php echo $icon; ?>.png"/></div>
            </div>

            <div class="col-md-9">				
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="folderName" class="control-label">Folder Name:</label>
                            <input type="text" class="form-control" name="folderName" id="folderName" value="<?php echo isset ( $folderName ) ? Validate::prepareOutput ( $folderName ) : ""; ?>" />
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="parentId" class="control-label">Parent Folder:</label>
                            <select class="form-control" name="parentId" id="parentId">
                                <option value="-1">- None -</option>
<?php
$currentFolderStr = ( $editFolderId !== null ? $folderListing [ $editFolderId ] : 0 );
foreach ( $folderListing AS $key => $value ) {
    if ( $editFolderId !== null ) {
        if ( substr ( $value, 0, strlen ( $currentFolderStr ) ) == $currentFolderStr ) {
            continue;
        }
    }

    echo "<option value=\"" . $key . "\"";
    if ( $parentId == $key ) {
        echo " selected";
    }
    echo ">" . Validate::prepareOutput ( $value ) . "</option>";
}
?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="hidden" name="submitme" id="submitme" value="1" />
        <?php if ( $editFolderId !== null ) { ?>
            <input type="hidden" value="<?php echo ( int ) $editFolderId; ?>" name="editFolderId" />
        <?php } ?>

        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-info" onClick="processAjaxForm ( this, function ( data ) { <?php echo $editFolderId == null ? "setUploaderFolderList ( data [ 'folder_listing_html' ] );" : ""; ?> loadImages ( data [ 'folder_id' ] ); refreshFolderListing ( false ); $( '.modal' ).modal ( 'hide' ); updateStatsViaAjax ( ); }); return false;"><?php echo $editFolderId == null ? "Add Folder" : "Update Folder"; ?> <i class="entypo-check"></i></button>
    </div>
</form>
