<?php

// Main configurations
define ( "CONFIG_SITE_HOST_URL", "www.localhost.com" ); // Site domain url without the protocol and no trailing forward slash (default: "localhost"; example: "www.mydomain.com")
define ( "CONFIG_SITE_URL", CONFIG_SITE_HOST_URL . "/ofileshare" ); // Site folder if any and no trailing forward slash (default: ""; example: "/site")
define ( "CONFIG_SITE_NAME", "OFileShare" ); // Site name (default: "OfileShare")
define ( "CONFIG_TIMEZONE", "Europe/Lisbon" ); // Site timezone (default: "GMT")
define ( "CONFIG_MAINTENANCE_MODE", false ); // Site maintenance mode (default: false)
define ( "CONFIG_REGISTRATION_ENABLE", true ); // Site enable user account registration (default: true)

// Database connection details
define ( "CONFIG_DB_DRIVER", "mysql" ); // Database driver (default: "mysql"; options: ["mysql"])
define ( "CONFIG_DB_HOST", "127.0.0.1" ); // Database hostname (default: "127.0.0.1")
define ( "CONFIG_DB_PORT", "3306" ); // Database port (default: "3306")
define ( "CONFIG_DB_NAME", "ofileshare" ); // Database schema ame (default: "database")
define ( "CONFIG_DB_USER", "ofileshare" ); // Database username (default: "root")
define ( "CONFIG_DB_PASS", "Qp8eVm4GdyS8JEmZ" ); // Database password (default: "")

// Session configurations
define ( "CONFIG_SESSION_NAME", "ofileshare" ); // Session name (default: "ofileshare")
define ( "CONFIG_SESSION_LIFETIME", 86400 ); // Session lifetime (default: 86400 [24 hours])

// reCAPTCHA configurations
define ( "CONFIG_CAPTCHA_ENABLED", true ); // reCAPTCHA enable/disable (default: true)
define ( "CONFIG_CAPTCHA_PUBLIC_KEY", "6LfLzCUUAAAAAFKvwkVvnC2zlm7w-CeTc2mp5Nef" ); // reCAPTCHA public key (default: "")
define ( "CONFIG_CAPTCHA_SECRET_KEY", "6LfLzCUUAAAAAKzk0GMr2RwjCos2Ggs9CpwOHtTh" ); // reCAPTCHA secret key (default: "")

// Email configurations
define ( "CONFIG_EMAIL_METHOD", "smtp" ); // Email method (default: "smtp"; options: ["smtp", "php"])
define ( "CONFIG_EMAIL_FROM_DEFAULT", "noreply@ofshare.x10.bz" ); // Email address used to send mails by defaul (default: "")
define ( "CONFIG_EMAIL_SMTP_HOST", "xo5.x10hosting.com" ); // Email smtp host (default: "")
define ( "CONFIG_EMAIL_SMTP_PORT", 465 ); // Email smtp port (default: 587)
define ( "CONFIG_EMAIL_SMTP_SECURE", "ssl" ); // Email smtp encryption system (default: ""; options: ["", "tls", "ssl"])
define ( "CONFIG_EMAIL_SMTP_USERNAME", "noreply@ofshare.x10.bz" ); // Email smtp username (default: "")
define ( "CONFIG_EMAIL_SMTP_PASSWORD", "cf;kO;8jh2cXVO>Z1?y`EYsUr4i74REN" ); // Email smtp password (default: "")
