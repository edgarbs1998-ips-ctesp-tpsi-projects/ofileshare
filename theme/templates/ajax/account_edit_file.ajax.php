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

$pageUrl = $file->getFullShortUrl ( );

// Load folders
$folderListing = FileFolder::loadAllForSelect ( $user->id );
?>

<form action="<?php echo WEB_ROOT; ?>/ajax/account_edit_file.process.ajax.php" autocomplete="off">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Edit Existing Item (<?php echo Validate::prepareOutput ( $file->fil_name, null, 55); ?>)</h4>
    </div>

    <div class="modal-body">
		<div class="row">
			
			<div class="col-md-3">
				<div class="modal-icon-left"><img src="<?php echo THEME_IMAGE_PATH; ?>/modal_icons/document_edit.png"/></div>
			</div>
			
			<div class="col-md-9">
	
				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
							<label for="folderName" class="control-label">Sharing Url:</label>
							<div class="input-group">
								<input type="text" class="form-control" value="<?php echo Validate::prepareOutput ( $pageUrl ); ?>" readonly/>
								<span class="input-group-btn">
									<button type="button" class="btn btn-primary" onClick="window.open('<?php echo Validate::prepareOutput ( $pageUrl ); ?>'); return false;"><i class="entypo-link"></i></button>
								</span>
							</div>
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
							<label for="filename" class="control-label">Filename</label>
							<input type="text" class="form-control" name="filename" id="filename" value="<?php echo Validate::prepareOutput ( $file->getFilenameExcExtension ( ) ); ?>"/>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
							<label for="folder" class="control-label">File Folder</label>
							<select class="form-control" name="folder" id="folder">
								<option value="">- Default -</option>
								<?php
								foreach ( $folderListing AS $key => $value ){
									echo "<option value='" . ( int ) $key . "'";
									if ( $file->fil_fol_id == ( int ) $key ) {
										echo " selected";
									}
									echo ">" . Validate::prepareOutput ( $value ) . "</option>";
								}
								?>
							</select>
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="col-md-12">
						<div class="form-group">
							<label for="filePrivacy" class="control-label">File Privacy</label>
							<select class="form-control" name="filePrivacy" id="filePrivacy">
								<?php
								$db->query ( "SELECT fpe_id, fpe_name FROM file_permission" );
								$rows = $db->getRows ( );
								if ( $rows ) {
									foreach ( $rows AS $row ){
										echo "<option value='" . ( int ) $row->fpe_id . "'";
										if ( $file->fil_fpe_id == ( int ) $row->fpe_id ) {
											echo " selected";
										}
										echo ">" . Validate::prepareOutput ( $row->fpe_name ) . "</option>";
									}
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
        <input type="hidden" value="<?php echo ( int ) $fileId; ?>" name="fileId" />
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-info" onClick="processAjaxForm(this, function() { reloadPreviousAjax(); $('.modal').modal('hide'); }); return false;">Update Item <i class="entypo-check"></i></button>
    </div>
</form>
