<?php

// Load config file
require_once ( DOC_ROOT . "/_config.inc.php" );

// Set default values for missing non required configs

// Main configurations
if ( !defined ( "CONFIG_SITE_NAME" ) ) {
    define ( "CONFIG_SITE_NAME", "OFileShare" );
}
if ( !defined ( "CONFIG_TIMEZONE" ) ) {
    define ( "CONFIG_TIMEZONE", "GMT" );
}
if ( !defined ( "CONFIG_MAINTENANCE_MODE" ) ) {
    define ( "CONFIG_MAINTENANCE_MODE", false );
}
if ( !defined ( "CONFIG_REGISTRATION_ENABLE" ) ) {
    define ( "CONFIG_REGISTRATION_ENABLE", true );
}

// Session configurations
if ( !defined ( "CONFIG_SESSION_NAME" ) ) {
    define ( "CONFIG_SESSION_NAME", "ofileshare" );
}
if ( !defined ( "CONFIG_SESSION_LIFETIME" ) ) {
    define ( "CONFIG_SESSION_LIFETIME", 86400 );
}

// reCAPTCHA configurations
if ( !defined ( "CONFIG_CAPTCHA_ENABLED" ) ) {
    define ( "CONFIG_CAPTCHA_ENABLED", true );
}

// Email configurations
if ( !defined ( "CONFIG_EMAIL_METHOD" ) ) {
    define ( "CONFIG_EMAIL_METHOD", "smtp" );
}
if ( !defined ( "CONFIG_EMAIL_SMTP_HOST" ) ) {
    define ( "CONFIG_EMAIL_SMTP_HOST", "smtp" );
}
if ( !defined ( "CONFIG_EMAIL_SMTP_PORT" ) ) {
    define ( "CONFIG_EMAIL_SMTP_PORT", 587 );
}
if ( !defined ( "CONFIG_EMAIL_SMTP_SECURE" ) ) {
    define ( "CONFIG_EMAIL_SMTP_SECURE", "" );
}
if ( !defined ( "CONFIG_EMAIL_SMTP_USERNAME" ) ) {
    define ( "CONFIG_EMAIL_SMTP_USERNAME", "" );
}
if ( !defined ( "CONFIG_EMAIL_SMTP_PASSWORD" ) ) {
    define ( "CONFIG_EMAIL_SMTP_PASSWORD", "" );
}

class Config {
    
    static private $requiredConfigs = array (
        // Main configurations
        "CONFIG_SITE_URL",

        // Database connection details
        "CONFIG_DB_DRIVER",
        "CONFIG_DB_HOST",
        "CONFIG_DB_PORT",
        "CONFIG_DB_NAME",
        "CONFIG_DB_USER",
        "CONFIG_DB_PASS",

        // reCAPTCHA configurations
        "CONFIG_CAPTCHA_PUBLIC_KEY",
        "CONFIG_CAPTCHA_SECRET_KEY",
        
        // Email configurations
        "CONFIG_EMAIL_FROM_DEFAULT",
    );

    static function checkRequiredConfigs ( ) {
        foreach ( self::$requiredConfigs AS $config ) {
            if ( !defined ( $config ) ) {
                throw new Exception ( "Required configuration constant '" . $config . "' is missing!" );
            }
        }
    }
}
