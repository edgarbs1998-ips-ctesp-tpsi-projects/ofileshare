<?php

// Setup response array
$rs = array (
    "error" => "",
    "login_status" => "invalid",
    "redirect_url" => "",
);

// Form validation
$username   = strtolower ( trim ( filter_input ( INPUT_POST, "username", FILTER_SANITIZE_STRING ) ) );
$password   = trim ( filter_input ( INPUT_POST, "password" ) );

if ( !strlen ( $username ) ) {
    $rs [ "error" ] = "Please enter your username";
} elseif ( !strlen ( $password ) ) {
    $rs [ "error" ] = "Please enter your password";
}

// Login validation
if ( !strlen ( $rs [ "error" ] ) ) {
    $result = $user->login ( $username, $password );
    if ( $result ) {
        $rs [ "redirect_url" ] = WEB_ROOT . "/index.html";
        $rs [ "login_status" ] = "success";
    } else {
        $rs [ "error" ] = "Your username and/or password are invalid";
    }
}

echo json_encode ( $rs );
