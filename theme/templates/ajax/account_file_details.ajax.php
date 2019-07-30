<?php
// For failed auth
$javascript = "";

// Load file
$userOwnsFile = false;
$folder = null;
if ( isset ( $_POST [ "f" ] ) ) {
    $file = File::loadById ( $_POST [ "f" ] );
    if ( !$file ) {
        // Failed lookup of file
        $returnJson = array ( );
        $returnJson [ "html" ] = "File not found.";
        $returnJson [ "javascript" ] = "window.location = '" . WEB_ROOT . "';";
        echo json_encode ( $returnJson );
        exit ( );
    }

    // Load folder
    $folder = $file->getFolderData();

    // Check if current user has permission to view the file
    if ( $file->usr_id != $user->id && $user->level < 20 ) {
        // If this is a private file
        if ( $file->fil_fpe_id != 2 ) {
            $returnJson [ "html" ] = "<div class=\"ajax-error-image\"><!-- --></div>";
            $returnJson [ "page_title" ] = "Error";
            $returnJson [ "page_url" ] = "";
            $returnJson [ "javascript" ] = "showErrorNotification ( 'Error', 'File is not publicly shared. Please contact the owner and request they update the privacy settings.' );";
            echo json_encode ( $returnJson );
            exit ( );
        }
    }

    if ( $user->isLogged ( ) && $file->usr_id == $user->id ) {
        $userOwnsFile = true;
    }
} else {
    $returnJson = array ( );
    $returnJson [ "html" ] = "No file.";
    $returnJson [ "javascript" ] = "window.location = '" . WEB_ROOT . "';";
    echo json_encode ( $returnJson );
    exit ( );
}

// Load folder
$folder = FileFolder::loadById ( $file->fil_fol_id, true );

// Get file owner details
$owner = User::loadUserById ( $file->usr_id );

// Public status
$isPublic = 0;
if ( $file->fil_fpe_id == 2 ) {
    $isPublic = 1;
}
?>

<?php
ob_start();
?>

<div class="file-browse-container-wrapper">
    <div class="file-preview-wrapper">
        <div class="row">
            <div class="col-md-12">
                <div class="section-wrapper">
                    <?php
                    $fileTypeLink = "/images/file_icons/160px/" . $file->fil_extension . ".png";
                    if ( file_exists ( SITE_THEME_DIRECTORY_ROOT . $fileTypeLink ) ) {
                        $iconLink = SITE_THEME_WEB_ROOT . "/" . $fileTypeLink;
                    }
                    ?>
                    <img width="60" class="img-rounded" alt="<?php echo Validate::prepareOutput ( $owner->getAccountScreenName ( ) ); ?>" src="<?php echo $iconLink; ?>" />
                    <span class="text-section">
                        <a href="#" class="text-section-1"><?php echo Validate::prepareOutput ( $file->fil_name ); ?> <small>(<?php echo Validate::prepareOutput ( $folder->fol_name ); ?>)</small></a> by <?php echo Validate::prepareOutput ( $owner->getAccountScreenName ( ) ); ?>
                    </span>

                    <?php if ( $isPublic ) { ?>
                        <div class="image-social-sharing">
                            <div class="row mobile-social-share">
                                <div id="socialHolder">
                                    <div id="socialShare" class="btn-group share-group">
                                        <a data-toggle="dropdown" class="btn btn-info">
                                            <i class="entypo-share"></i>
                                        </a>
                                        <button href="#" data-toggle="dropdown" class="btn btn-info dropdown-toggle share">
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="https://twitter.com/intent/tweet?url=<?php echo Validate::prepareOutput ( $file->getFullShortUrl ( ) ); ?>&text=<?php echo Validate::prepareOutput ( $file->fil_name ); ?>" data-original-title="Twitter" data-toggle="tooltip" href="#" class="btn btn-twitter" data-placement="left" target="_blank">
                                                    <i class="fa fa-twitter"></i>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo Validate::prepareOutput ( $file->getFullShortUrl ( ) ); ?>" data-original-title="Facebook" data-toggle="tooltip" href="#" class="btn btn-facebook" data-placement="left" target="_blank">
                                                    <i class="fa fa-facebook"></i>
                                                </a>
                                            </li>					
                                            <li>
                                                <a href="https://plus.google.com/share?url=<?php echo Validate::prepareOutput ( $file->getFullShortUrl ( ) ); ?>" data-original-title="Google+" data-toggle="tooltip" href="#" class="btn btn-google" data-placement="left" target="_blank">
                                                    <i class="fa fa-google-plus"></i>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="http://www.linkedin.com/shareArticle?mini=true&url=<?php echo Validate::prepareOutput ( $file->getFullShortUrl ( ) ); ?>" data-original-title="LinkedIn" data-toggle="tooltip" href="#" class="btn btn-linkedin" data-placement="left" target="_blank">
                                                    <i class="fa fa-linkedin"></i>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="http://pinterest.com/pin/create/button/?url=<?php echo Validate::prepareOutput ( $file->getFullShortUrl ( ) ); ?>" data-original-title="Pinterest" data-toggle="tooltip" class="btn btn-pinterest" data-placement="left" target="_blank">
                                                    <i class="fa fa-pinterest"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <div class="section-wrapper">
                    <table class="table table-bordered table-striped">
                        <tbody>
                            <tr>
                                <td class="view-file-details-first-row">Owner:</td>
                                <td class="responsiveTable">
                                    <?php echo Validate::prepareOutput ( $file->getOwnerUsername ( ) ); ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="view-file-details-first-row">Uploaded:</td>
                                <td class="responsiveTable">
                                    <?php echo $file->fil_upload_date; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="view-file-details-first-row">Filesize:</td>
                                <td class="responsiveTable">
                                    <?php echo Functions::formatSize ( $file->fil_size ); ?>
                                </td>
                            </tr>

                            <?php if ( $userOwnsFile && $file->fil_trash == null ) { ?>
                                <tr>
                                    <td class="view-file-details-first-row">Privacy:</td>
                                    <td class="responsiveTable">
                                        <?php echo ( $isPublic ) ? "<i class=\"entypo-lock-open\"></i>" : "<i class=\"entypo-lock\"></i>"; ?>
                                        <?php echo ( $isPublic ) ? "Public - Ccan be accessed by everyone with the link" : "Private - Can only be accessed by the owner"; ?>
                                    </td>
                                </tr>
                            <?php } ?>

                            <tr>
                                <td class="view-file-details-first-row">Status:</td>
                                <td class="responsiveTable"><?php echo $file->fil_trash == null ? "Active" : "Deleted"; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php
                $links = array ( );
                if ( $userOwnsFile ) {
                    if ( $file->fil_trash == null ) {
                        $links [ ] = '<button type="button" class="btn btn-default" data-dismiss="modal" onClick="showEditFileForm(' . ( int ) $file->fil_id . '); return false;" title="" data-original-title="Edit File" data-placement="bottom" data-toggle="tooltip"><i class="entypo-pencil"></i></button>';
                        $links [ ] = '<button type="button" class="btn btn-default" data-dismiss="modal" onClick="deleteFile(' . ( int ) $file->fil_id . ', function() {loadImages(' . ( ( int ) $file->fil_fol_id ? $file->fil_fol_id : "-1" ) . ');}); return false;" title="" data-original-title="Delete File" data-placement="bottom" data-toggle="tooltip"><i class="entypo-trash"></i></button>';
                    }
                }

                if ( $file->fil_trash == null ) {
                    $links [ ] = '<button type="button" class="btn btn-info" onClick="triggerFileDownload(' . ( int ) $file->fil_id . ', \'' . $file->getFileHash ( ) . '\'); return false;">Download <i class="entypo-down"></i></button>';
                }
                ?>

                <?php if ( count ( $links ) ) { ?>
                    <div class="section-wrapper">
                        <div class="button-wrapper responsiveMobileAlign">						
                            <?php foreach ( $links AS $link ) { ?>
                                <div class="btn-group responsiveMobileMargin">
                                    <?php echo $link; ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>

                <?php if ( $file->fil_trash == null ) { ?>
                    <div role="tabpanel">
                        <ul class="nav nav-tabs file-info-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#details" aria-controls="details" role="tab" data-toggle="tab"><i class="entypo-share"></i><span> Sharing Code</span></a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="details">
                                <h4><strong>File Page Link</strong></h4>
                                <pre><section onClick="selectAllText(this); return false;"><?php echo $file->getFullShortUrl ( ); ?></section></pre>

                                <h4><strong>HTML Code</strong></h4>
                                <pre><section onClick="selectAllText(this); return false;"><?php echo $file->getHtmlLinkCode ( ); ?></section></pre>

                                <h4><strong>Forum Code</strong></h4>
                                <pre><section onClick="selectAllText(this); return false;"><?php echo $file->getForumLinkCode ( ); ?></section></pre>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<?php
$html = ob_get_contents();
ob_end_clean();

// prepare result
$returnJson = array ( );
$returnJson [ "success" ] = true;
$returnJson [ "html" ] = $html;
$returnJson [ "page_title" ] = $file->fil_name;
$returnJson [ "page_url" ] = $file->getFullShortUrl ( );
$returnJson [ "javascript" ] = $javascript;

echo json_encode ( $returnJson );
exit ( );
