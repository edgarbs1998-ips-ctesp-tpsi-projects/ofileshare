<?php

// Setup response array
$rs = array (
    "error" => "",
    "forgot_password_status" => "invalid",
    "redirect_url" => "",
);

// Form validation
$password           = trim ( filter_input ( INPUT_POST, "password" ) );
$passwordConfirm    = trim ( filter_input ( INPUT_POST, "passwordConfirm" ) );
$userId             = ( int ) trim ( filter_input ( INPUT_POST, "u", FILTER_SANITIZE_NUMBER_INT ) );
$userIp             = trim ( filter_input ( INPUT_POST, "i" ) );
$passwordResetHash  = trim ( filter_input ( INPUT_POST, "h" ) );

$userAccount = User::loadUserByPasswordResetHash ( $passwordResetHash );
if ( !$userAccount || $userId != $userAccount->usr_id ) {
	$rs [ "error" ] = "Something went wrong. Your password has not been reset";
} elseif ( !strlen ( $password ) ) {
    $rs [ "error" ] = "Please enter your new password";
} elseif ( strcmp ( $password, $passwordConfirm ) ) {
    $rs [ "error" ] =  "Your password confirmation does not match ";
} elseif ( ( $result = Validate::validatePassword ( $passwordConfirm ) ) !== true ) {
    $rs [ "error" ] =  $result;
}

// Reset password
if ( !strlen ( $rs [ "error" ] ) ) {
    $db = Database::getInstance ( );

    try {
        $db->query ( "CALL sp_user_update_password ( :user_id, :user_ip, :password )", array ( "user_id" => $userId, "user_ip" => $userIp, "password" => password_hash ( $password, PASSWORD_BCRYPT  ) ) );

        $rs [ "redirect_url" ] = WEB_ROOT . "/login.html?s=1";
        $rs [ "forgot_password_status" ] = "success";
    } catch ( APPException $ex ) {
        $ex->log ( );

        $rs [ "error" ] = "Something went wrong. Your password has not been reset";
    }
}

echo json_encode ( $rs );
