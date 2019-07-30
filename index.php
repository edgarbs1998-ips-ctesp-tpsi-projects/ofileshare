<?php

// Load system core module
require_once ( "core/includes/core.inc.php" );

// Include template
if ( isset ( $_GET [ "page" ] ) ) {
    $page = $_GET [ "page" ];
} else {
    $page = "index.html";
}
$page = str_replace ( "../", "", $page );
define ( "PAGE_URL", $page );

$templateFile = THEME_TEMPLATES_PATH . "/" . $page;
if ( file_exists ( $templateFile ) && is_file ( $templateFile ) ) {
    require_once ( $templateFile );
    exit ( );
}

if ( substr ( $page, strlen ( $page ) - 1, 1) == "/" ) {
	$filePath = THEME_TEMPLATES_PATH . "/" . $page . "index.html";
	if ( file_exists ( $filePath ) && is_file ( $filePath ) ) {
		require_once ( $filePath );
		exit;
	}
}

$subPath = current ( explode ( "/", $page ) );
if ( file_exists ( THEME_TEMPLATES_PATH . "/" . $subPath ) ) {
	if ( file_exists ( THEME_TEMPLATES_PATH . "/" . $subPath . "/index.html" ) ) {
		include_once ( THEME_TEMPLATES_PATH . "/" . $subPath . "/index.html" );
	}
}

require_once ( CORE_PAGE_DIRECTORY_ROOT . "/file_download.php" );
exit ( );
