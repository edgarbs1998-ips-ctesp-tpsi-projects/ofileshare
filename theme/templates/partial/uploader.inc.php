<?php
define ( "USE_CHUNKED_UPLOADS", true );
define ( "CHUNKED_UPLOAD_SIZE", 104857600 ); // 100MB

$userMaxUploadSize = User::getMaxUploadFilesize ( $user->id );

// Load folders
$folderArr = array ( );
if ( $user->isLogged ( ) ) {
    $folderArr = FileFolder::loadAllForSelect ( $user->id );
}

// uploader javascript
require_once ( THEME_TEMPLATES_PATH . "/partial/uploader_javascript.inc.php" );
?>

<div class="preLoadImages hidden">
    <img src="<?php echo THEME_IMAGE_PATH; ?>/delete_small.png" height="1" width="1"/>
    <img src="<?php echo THEME_IMAGE_PATH; ?>/add_small.gif" height="1" width="1"/>
    <img src="<?php echo THEME_IMAGE_PATH; ?>/red_error_small.png" height="1" width="1"/>
    <img src="<?php echo THEME_IMAGE_PATH; ?>/green_tick_small.png" height="1" width="1"/>
    <img src="<?php echo THEME_IMAGE_PATH; ?>/processing_small.gif" height="1" width="1"/>
</div>

<div>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <ul class="nav nav-tabs bordered">
        <li class="active"><a href="#fileUpload" data-toggle="tab">File Upload</a></li>
    </ul>

    <!-- FILE UPLOAD -->
    <div class="tab-content">
        <div id="fileUpload" class="tab-pane active">
            <div class="fileUploadMain">
                <div>
                    <!-- uploader -->
                    <div id="uploaderContainer" class="uploader-container">
                        <div id="uploader">
                            <form method="post" enctype="multipart/form-data">
                                <div class="fileupload-buttonbar hiddenAlt">
                                    <label class="fileinput-button">
                                        <span>Add files...</span>
                                        <?php
                                        if ( Functions::checkBrowserSupportsMultipleUploads ( ) ) {
                                            echo '<input id="add_files_btn" type="file" name="files[]" multiple>';
                                        } else {
                                            echo '<input id="add_files_btn" type="file" name="files">';
                                        }
                                        ?>
                                    </label>
                                    <button id="start_upload_btn" type="submit" class="start">Start upload</button>
                                    <button id="cancel_upload_btn" type="reset" class="cancel">Cancel upload</button>
                                </div>
                                <div class="fileupload-content">
                                    <label for="add_files_btn" id="initialUploadSectionLabel">
                                        <div id="initialUploadSection" class="initialUploadSection"<?php echo !Functions::currentBrowserIsIE ( ) ? " onClick=\"$('#add_files_btn').click(); return false;\"" : ""; ?>>
                                            <div class="initialUploadText">
                                                <div class="uploadElement">
                                                    <div class="internal">
                                                        <img src="<?php echo THEME_IMAGE_PATH; ?>/modal_icons/upload-computer-icon.png" class="upload-icon-image"/>
                                                        <div class="clear"><!-- --></div>
														<?php if ( Functions::currentBrowserIsIE ( ) ) { ?>
															Click here to browse your files...
														<?php } else { ?>
                                                            Drag &amp; drop files here or click to browse...
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="uploadFooter">
                                                <div class="baseText">
                                                    <a class="showAdditionalOptionsLink">Options</a>&nbsp;&nbsp;|&nbsp;&nbsp;Max file size: <?php echo $userMaxUploadSize > 0 ? Functions::formatSize ( $userMaxUploadSize ) : "Unlimited"; ?>.
                                                </div>
                                            </div>
                                            <div class="clear"><!-- --></div>
                                        </div>
                                    </label>
                                    <div id="fileListingWrapper" class="fileListingWrapper hidden">
                                        <div class="fileSection">
                                            <div id="files" class="files"></div>
                                            <div id="addFileRow" class="addFileRow">
                                                <div class="template-upload template-upload-img">
                                                    <a href="#"<?php echo !Functions::currentBrowserIsIE ( ) ? " onClick=\"$('#add_files_btn').click(); return false;\"" : ""; ?>>
                                                        <i class="glyphicon glyphicon-plus"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="clear"></div>
                                        </div>

                                        <div id="processQueueSection" class="fileSectionFooterText">
                                            <div class="upload-button">
                                                <button onClick="$('#start_upload_btn').click(); return false;" class="btn btn-green btn-lg" type="button">Upload Queue <i class="entypo-upload"></i></button>
                                            </div>
                                            <div class="baseText">
                                                <a class="showAdditionalOptionsLink">Options</a>&nbsp;&nbsp;|&nbsp;&nbsp;Max file size: <?php echo $userMaxUploadSize > 0 ? Functions::formatSize ( $userMaxUploadSize ) : "Unlimited"; ?>.
                                            </div>
                                            <div class="clear"><!-- --></div>
                                        </div>

                                        <div id="processingQueueSection" class="fileSectionFooterText hidden">
                                            <div class="globalProgressWrapper">
                                                <div id="progress" class="progress progress-striped active">
                                                    <div style="width: 0%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="0" role="progressbar" class="progress-bar progress-bar-info">
                                                        <span class="sr-only"></span>
                                                    </div>
                                                </div>
                                                <div id="fileupload-progresstext" class="fileupload-progresstext">
                                                    <div id="fileupload-progresstextRight" class="file-upload-progress-right"><!-- --></div>
                                                    <div id="fileupload-progresstextLeft" class="file-upload-progress-left"><!-- --></div>
                                                </div>
                                            </div>
                                            <div class="clear"><!-- --></div>
                                            <div class="upload-button">
                                                <button id="hide_modal_btn" data-dismiss="modal" class="btn btn-default btn-lg" type="button">Hide <i class="entypo-arrows-ccw"></i></button>
                                            </div>
                                            <div class="clear"><!-- --></div>
                                        </div>

                                        <div id="completedSection" class="fileSectionFooterText row hidden">
                                            <div class="col-md-12">
                                                <div class="baseText">
                                                    File uploads completed. <a href="<?php echo WEB_ROOT; ?>/index.html?upload=1">Click here</a> to upload more files.
                                                </div>
                                            </div>
											
											<div class="col-md-12 upload-complete-btns">
                                                <button class="btn btn-info" type="button" onClick="viewFileLinksPopup(); return false;">View All Links <i class="entypo-link"></i></button>
                                                <button data-dismiss="modal" class="btn btn-default" type="button">Close <i class="entypo-check"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <script id="template-upload" type="text/x-jquery-tmpl">
                            {% for (var i=0, file; file=o.files[i]; i++) { %}
                            <div class="template-upload-img template-upload{% if (file.error) { %} errorText{% } %}" id="fileUploadRow{%=i%}" title="{%=file.name%}">
                            {% if (file.error) { %}
                            <div class="error cancel" title="{%=file.name%}">Error
                            {%=file.error%}
                            </div>
                            {% } else { %}
                            <div class="previewOverlay" title="{%=file.name%}">
                            <div class="progressText hidden"></div>
                            <div class="progress hidden">
                            <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                            </div>
                            </div>
                            </div>
                            <div class="previewWrapper" title="{%=file.name%}">
                            <div class="cancel">
                            <a href="#" onClick="return false;">
                            <img src="<?php echo THEME_IMAGE_PATH; ?>/delete_small.png" height="10" width="10" alt="delete"/>
                            </a>
                            </div>
                            <div class="preview" title="{%=file.name%}&nbsp;&nbsp;{%=o.formatFileSize(file.size)%}"><span class="fade"></span></div>
							<div class="filename" title="{%=file.name%}&nbsp;&nbsp;{%=o.formatFileSize(file.size)%}">{%=file.name%}</div>
                            </div>
                            <div class="start hidden"><button>start</button></div>
                            <div class="cancel hidden"><button>cancel</button></div>
                            {% } %}
                            </div>
                            {% } %}
                        </script>

                        <script id="template-download" type="text/x-jquery-tmpl"><!-- --></script>
                    </div>
                    <!-- end uploader -->
                </div>

                <div class="clear"><!-- --></div>
            </div>
        </div>
    </div>
</div>


<div id="additionalOptionsWrapper" class="additional-options-wrapper" style="display: none;">
    <div class="row">
		<div class="col-md-2"></div>

        <div class="col-md-8">
            <div class="panel minimal">
                <div class="panel-heading">
                    <div class="panel-title">Store in Folder:</div>
                </div>
                <div class="panel-body">
                    <p>Select an folder below to store these files in. All current uploads will be available within these folders.</p>
                    <div class="form-group">
                        <label class="control-label" for="upload_folder_id">Folder Name:</label>
                        <select id="upload_folder_id" name="upload_folder_id" class="form-control" <?php echo !$user->isLogged ( ) ? 'disabled' : ''; ?>>
                            <option value=""><?php echo !$user->isLogged() ? "- login to enable -" : "- default -"; ?></option>
                            <?php
                            if ( count ( $folderArr ) ) {
                                foreach ( $folderArr AS $id => $folderLabel ) {
                                    echo '<option value="' . ( int ) $id . '"';
                                    if ( $fid == ( int ) $id ) {
                                        echo ' selected';
                                    }
                                    echo '>' . Validate::prepareOutput ( $folderLabel ) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

		<div class="col-md-2"></div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="footer-buttons">
                <button onClick="showAdditionalOptions(true);
                        return false;" class="btn btn-default" type="button">Cancel</button>
                <button onClick="saveAdditionalOptions();
                        return false;" class="btn btn-info" type="button">Save Options <i class="entypo-check"></i></button>
            </div>
        </div>
    </div>
</div>
