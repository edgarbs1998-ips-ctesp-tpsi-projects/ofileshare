<!DOCTYPE html>
<html lang="en" class="<?php echo defined ( "HTML_ELEMENT_CLASS" ) ? HTML_ELEMENT_CLASS : ""; ?>">
    <head>
        <noscript>
            <meta http-equiv="refresh" content="0;url=<?php echo WEB_ROOT; ?>/nojs.html" />
        </noscript>

        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo Validate::prepareOutput ( PAGE_NAME ); ?> - <?php echo CONFIG_SITE_NAME; ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="description" content="<?php echo Validate::prepareOutput ( PAGE_DESCRIPTION ); ?>" />
        <meta name="keywords" content="<?php echo Validate::prepareOutput ( PAGE_KEYWORDS ); ?>" />
        <meta name="copyright" content="Copyright &copy; <?php echo date ( "Y" ); ?> - <?php echo CONFIG_SITE_NAME; ?>" />
        <meta name="robots" content="all" />
        <meta http-equiv="Cache-Control" content="no-cache" />
        <meta http-equiv="Expires" content="-1" />
        <meta http-equiv="Pragma" content="no-cache" />
		
		<!-- fav and touch icons -->
        <link rel="icon" type="image/x-icon" href="<?php echo THEME_IMAGE_PATH; ?>/favicon/favicon.ico" />
        <link rel="icon" type="image/png" sizes="96x96" href="<?php echo THEME_IMAGE_PATH; ?>/favicon/favicon-96x96.png">
        <link rel="apple-touch-icon" sizes="152x152" href="<?php echo THEME_IMAGE_PATH; ?>/favicon/favicon-152x152.png">
        <link rel="manifest" href="<?php echo THEME_IMAGE_PATH; ?>/favicon/manifest.json">
        <meta name="msapplication-TileImage" content="<?php echo THEME_IMAGE_PATH; ?>/favicon/favicon-144x144.png">
        <meta name="msapplication-TileColor" content="#1a8fbf">
        <meta name="theme-color" content="#1a8fbf">
        
        <link rel="stylesheet" href="<?php echo THEME_CSS_PATH; ?>/fonts.css" type="text/css" charset="utf-8" />
        <link rel="stylesheet" href="<?php echo THEME_CSS_PATH; ?>/font-icons/entypo/css/entypo.css" type="text/css" charset="utf-8" />
        <link rel="stylesheet" href="<?php echo THEME_CSS_PATH; ?>/font-icons/font-awesome/css/font-awesome.min.css" type="text/css" charset="utf-8" />
        <link rel="stylesheet" href="<?php echo THEME_CSS_PATH; ?>/bootstrap.css" type="text/css" charset="utf-8" />
        <link rel="stylesheet" href="<?php echo THEME_CSS_PATH; ?>/skins/default.css" type="text/css" charset="utf-8" />
        <link rel="stylesheet" href="<?php echo THEME_CSS_PATH; ?>/core.css" type="text/css" charset="utf-8" />
        <link rel="stylesheet" href="<?php echo THEME_CSS_PATH; ?>/theme.css" type="text/css" charset="utf-8" />
        <link rel="stylesheet" href="<?php echo THEME_CSS_PATH; ?>/forms.css" type="text/css" charset="utf-8" />
        <link rel="stylesheet" href="<?php echo THEME_CSS_PATH; ?>/responsive.css" type="text/css" charset="utf-8" />
        <link rel="stylesheet" href="<?php echo THEME_CSS_PATH; ?>/daterangepicker-bs3.css" type="text/css" charset="utf-8" />
		<link rel="stylesheet" href="<?php echo THEME_CSS_PATH; ?>/custom.css" type="text/css" charset="utf-8" />
        <link rel="stylesheet" href="<?php echo THEME_CSS_PATH; ?>/file-upload.css" type="text/css" charset="utf-8" />
        <link rel="stylesheet" href="<?php echo THEME_CSS_PATH; ?>/search_widget.css" type="text/css" charset="utf-8" />

        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/jquery-1.11.0.min.js"></script>
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/jquery.ckie.js"></script>
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/jquery.jstree.js"></script>
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/jquery.event.drag-2.2.js"></script>
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/jquery.event.drag.live-2.2.js"></script>
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/jquery.event.drop-2.2.js"></script>
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/jquery.event.drop.live-2.2.js"></script>
		
		<link rel="stylesheet" href="<?php echo THEME_CSS_PATH; ?>/file_browser_sprite_48px.css" type="text/css" charset="utf-8" />
        
        <link rel="stylesheet" href="<?php echo THEME_JS_PATH; ?>/slick/slick.css" type="text/css" charset="utf-8" />

        <link rel="stylesheet" href="<?php echo THEME_JS_PATH; ?>/slick/slick-theme.css" type="text/css" charset="utf-8" />       
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/slick/slick.js"></script>

        <link rel="stylesheet" href="<?php echo THEME_JS_PATH; ?>/photo-swipe/photoswipe.css" type="text/css" charset="utf-8" />       
        <link rel="stylesheet" href="<?php echo THEME_JS_PATH; ?>/photo-swipe/default-skin/default-skin.css" type="text/css" charset="utf-8" />       
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/photo-swipe/photoswipe.min.js"></script>
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/photo-swipe/photoswipe-ui-default.min.js"></script>
		
		<!-- mobile swipe navigation -->
		<script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/jquery.touchSwipe.min.js"></script>
		
		<script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/cloudable.js"></script>

        <!--[if lt IE 9]><script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/ie8-responsive-file-warning.js"></script><![endif]-->

        <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
                <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
                <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
        
        <script type="text/javascript">
            var WEB_ROOT = "<?php echo WEB_ROOT; ?>";
            var SITE_THEME_WEB_ROOT = "<?php echo SITE_THEME_WEB_ROOT; ?>";
            var SITE_CSS_PATH = "<?php echo THEME_CSS_PATH; ?>";
            var SITE_IMAGE_PATH = "<?php echo THEME_IMAGE_PATH; ?>";
            var CORE_AJAX_WEB_ROOT = "<?php echo CORE_AJAX_WEB_ROOT; ?>";
            var LOGGED_IN = <?php echo $user->isLogged ( ) ? "true" : "false"; ?>;
        </script>
        
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/jquery-ui.js"></script>
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/jquery.tmpl.min.js"></script>
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/load-image.min.js"></script>
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/canvas-to-blob.min.js"></script>
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/jquery.iframe-transport.js"></script>
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/jquery.fileupload.js"></script>
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/jquery.fileupload-process.js"></script>
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/jquery.fileupload-resize.js"></script>
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/jquery.fileupload-validate.js"></script>
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/jquery.fileupload-ui.js"></script>
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/zeroClipboard/ZeroClipboard.js"></script>
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/daterangepicker/moment.min.js"></script>
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/daterangepicker/daterangepicker.js"></script>
        <script type="text/javascript" src="<?php echo THEME_JS_PATH; ?>/global.js"></script>
    </head>