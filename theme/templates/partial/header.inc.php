<?php
// Local template functions
require_once ( THEME_TEMPLATES_PATH . "/partial/template_functions.inc.php" );

// Get database connection
$db = Database::getInstance ( );

// Load all files
$db->query ( "CALL sp_file_all_status ( :user_id )", array ( "user_id" => $user->id ) );
$fileRows = $db->getRows ( );
$totalActive = 0;
$totalActiveFileSize = 0;
$totalTrash = 0;
if ( !empty ( $fileRows ) ) {
    foreach ( $fileRows AS $row ) {
        if ( $row->status == "active" ) {
            $totalActive = (int) $row->total;
            $totalActiveFileSize = (int) $row->total_size;
        } else {
            $totalTrash = (int) $row->total;
        }
    }
}

// Calculate account stats
$totalFileStorage = User::getMaxFileStorage ( $user->id );
$storagePercentage = 0;
if ( $totalActiveFileSize > 0 && $totalFileStorage > 0 ) {
    $storagePercentage = ( $totalActiveFileSize / $totalFileStorage ) * 100;
    if ( $storagePercentage < 1 ) {
        $storagePercentage = 1;
    } else {
        $storagePercentage = floor ( $storagePercentage );
    }
} else {
    $storagePercentage = 0;
}

// Include header top
require_once ( THEME_TEMPLATES_PATH . "/partial/header_top.inc.php" );
?>

<body class="page-body">
    <div class="page-container horizontal-menu with-sidebar fit-logo-with-sidebar logged-in">	
        <div class="sidebar-menu fixed">
			<div class="sidebar-mobile-menu visible-xs">
                <a href="#" class="with-animation"><i class="entypo-menu"></i></a>
            </div>
			<div class="sidebar-mobile-upload visible-xs">
                <a href="#" onClick="uploadFiles(); return false;">Upload&nbsp;&nbsp;<span class="glyphicon glyphicon-cloud-upload"></span></a>
            </div>
			
            <!-- logo -->
            <div class="siderbar-logo">
                <a href="<?php echo WEB_ROOT; ?>/index.html">
                    <img src="<?php echo THEME_IMAGE_PATH; ?>/logo/logo.png" alt="<?php echo CONFIG_SITE_NAME; ?>" />
                </a>
            </div>
			<div id="folderTreeview"></div>
            <div class="clear"></div>
        </div>

        <header class="navbar navbar-fixed-top"><!-- set fixed position by adding class "navbar-fixed-top" -->
            <div class="navbar-inner">
                <div class="navbar-form navbar-form-sm navbar-left shift" ui-shift="prependTo" data-target=".navbar-collapse" role="search">
                    <div class="form-group">
                        <div class="input-group" id="top-search">
                            <input type="text" value="<?php echo isset ( $_GET [ "t" ] ) ? Validate::prepareOutput ( $_GET [ "t" ] ) : ""; ?>" class="form-control input-sm bg-light no-border rounded padder typeahead" placeholder="Search your files..." onKeyUp="handleTopSearch(event, this); return false;" id="searchInput">
                            <span class="input-group-btn">
                                <button type="submit" class="btn btn-sm bg-light rounded" onClick="handleTopSearch(null, $('#searchInput')); return false;" title="" data-original-title="Filter" data-placement="bottom" data-toggle="tooltip"><i class="entypo-search"></i></button>
                                <button type="submit" class="btn btn-sm bg-light rounded" onClick="showFilterModal(); return false;" title="" data-original-title="Advanced Search" data-placement="bottom" data-toggle="tooltip"><i class="entypo-cog"></i></button>
                            </span>
                        </div>
                    </div>
                </div>

				<div class="upload-button-wrapper pull-left">
					<button class="btn btn-green" type="button" onClick="setCurrentAlbumAsUploadFolder(); uploadFiles(); return false;">Upload&nbsp;&nbsp;<span class="glyphicon glyphicon-cloud-upload"></span></button>
				</div>

                <ul class="mobile-account-toolbar-wrapper nav navbar-right pull-right">
                    <?php
                    if ( $user->requireAccessLevel ( 20 ) ) {
                    ?>
                    <li class="root-level responsive-Hide">
                        <a href="#">
                            <span class="badge badge-danger badge-roundless">Admin User</span>
                        </a>
                    </li>
                    <?php } ?>
                    
                    <li class="dropdown account-nav-icon">
                        <a href="#" data-toggle="dropdown" class="dropdown-toggle clear">
                            <span class="user-screen-name hidden-sm hidden-md"><?php echo Validate::prepareOutput ( $user->getAccountScreenName ( ) ); ?></span> <b class="caret"></b>
                        </a>

                        <!-- dropdown -->
                        <ul class="dropdown-menu">
                            <?php
                            $label = "Unlimited";
                            if ( $totalFileStorage > 0 ) {
                                $label = $storagePercentage . '%';
                            }
                            ?>

                            <li class="account-menu bg-light" title="<?php echo $label; ?>" onClick="window.location='<?php echo WEB_ROOT; ?>/account_edit.html" style="cursor: pointer;">
                                <div>
                                    <p>
                                        <?php if ( $totalFileStorage > 0 ) { ?>
                                            <span><span id="totalActiveFileSize"><?php echo Validate::prepareOutput ( Functions::formatSize ( $totalActiveFileSize ) ); ?></span> of <?php echo Validate::prepareOutput ( Functions::formatSize ( $totalFileStorage ) ); ?> used</span>
                                        <?php } else { ?>
                                            <span><span id="totalActiveFileSize"><?php echo Validate::prepareOutput ( Functions::formatSize ( $totalActiveFileSize ) ); ?></span> of Unlimited</span>
                                        <?php } ?>
                                    </p>
                                </div>
                                <div class="progress progress-xs m-b-none dker">
                                    <div style="width: <?php echo $storagePercentage; ?>%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="<?php echo $storagePercentage; ?>" role="progressbar" class="progress-bar progress-bar-success"></div>
                                </div>
                            </li>
                            <li>
                                <a href="<?php echo WEB_ROOT; ?>/account_edit.html"> <i class="entypo-cog"></i>Account Settings</a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a href="<?php echo WEB_ROOT; ?>/logout.html"> <i class="entypo-logout"></i>Logout</a>
                            </li>
                        </ul>
                        <!-- / dropdown -->
                    </li>
                </ul>
            </div>
        </header>
        
        <div id="main-ajax-container" class="layer"></div>

        <?php
        require_once ( THEME_TEMPLATES_PATH . "/partial/account_home_javascript.inc.php" );
        ?>
