<?php

// Site debug mode (default: false)
define ( "DEBUG_MODE", false );

// Check PHP version
if ( version_compare ( PHP_VERSION, "5.4.0", "<" ) ) {
    exit ( "PHP version 5.4.0 or later is required! Current version: " . phpversion ( ) );
}

// Check PHP Magic Quotes
if ( get_magic_quotes_gpc ( ) ) {
    exit ( "PHP Magic Quotes are not supported and must be disabled!" );
}

// Setup error reporting
if ( DEBUG_MODE ) {
    error_reporting ( E_ALL | E_STRICT );
    ini_set ( "display_startup_errors", true );
    ini_set ( "display_errors", true );
} else {
    error_reporting ( E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED );
    ini_set ( "display_startup_errors", false );
    ini_set ( "display_errors", false );
}

// Determine document paths
define ( "DOC_ROOT", realpath ( dirname ( __FILE__ ) . "/../../" ) );
define ( "CORE_ROOT", DOC_ROOT . "/core" );
define ( "CORE_PAGE_DIRECTORY_ROOT", CORE_ROOT . "/pages");

// Increase script allocated memory
@ini_set ( "memory_limit", "128M" );

// Setup session security
ini_set ( "session.use_cookies", true );
ini_set ( "session.use_trans_sid", false );
ini_set ( "session.use_strict_mode", true );

// Load composer autoloader
require_once ( CORE_ROOT . "/vendor/autoload.php" );

// Load autoloader
require_once ( CORE_ROOT . "/includes/autoload.inc.php" );

// Check if all required configs are present
try {
    Config::checkRequiredConfigs ( );
} catch ( Exception $ex ) {
    die ( $ex->getMessage ( ) );
}

// Set timezone
date_default_timezone_set ( CONFIG_TIMEZONE );

// Determine web paths
define ( "WEB_ROOT", "http://" . CONFIG_SITE_URL );

// How often to update the download tracker in seconds
define ( "DOWNLOAD_TRACKER_UPDATE_FREQUENCY", 15 );

// How long to keep the download tracker data, in days
define ( "DOWNLOAD_TRACKER_PURGE_PERIOD", 7 );

// Cache Ip2Country database (required only once)
// Note: IPv6 is not supported at this momment
//$ip2Country = new Ip2Country;
//$ip2Country->parseCSV ( CORE_ROOT . "/cache/ip2country/IpToCountry.csv" );
//$ip2Country->parseCSV ( CORE_ROOT . "/cache/ip2country/IpToCountry.6R.csv" );

// Setup database connection
try {
    $db = Database::getInstance ( );
} catch ( APPException $ex ) {
    $ex->display ( );
}

// Register database session handler
DBSession::register ( );

// Setup session name
session_name ( CONFIG_SESSION_NAME );

// Setup session cookie params
session_set_cookie_params ( CONFIG_SESSION_LIFETIME, "/", CONFIG_SITE_HOST_URL, false, true );

// Start session
session_start ( );

// Setup user auth
$user = User::getInstance ( );

// Check if in maintenance mode
Functions::checkMaintenanceMode ( );

// Determine theme paths
define ( "SITE_THEME_DIRECTORY_ROOT", DOC_ROOT . "/theme/" );
define ( "SITE_THEME_WEB_ROOT", WEB_ROOT . "/theme/" );
define ( "THEME_IMAGE_PATH", SITE_THEME_WEB_ROOT . "images");
define ( "THEME_CSS_PATH", SITE_THEME_WEB_ROOT . "styles");
define ( "THEME_JS_PATH", SITE_THEME_WEB_ROOT . "js");
define ( "THEME_TEMPLATES_PATH", SITE_THEME_DIRECTORY_ROOT . "templates" );

// Determine core ajax path
define ( "CORE_APPLICATION_WEB_ROOT", WEB_ROOT . "/core" );
define ( "CORE_PAGE_WEB_ROOT", CORE_APPLICATION_WEB_ROOT . "/pages" );
define ( "CORE_AJAX_WEB_ROOT", CORE_PAGE_WEB_ROOT . "/ajax" );
