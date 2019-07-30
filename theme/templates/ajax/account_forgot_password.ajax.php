<?php

// Setup response array
$rs = array (
    "error" => "",
    "forgot_password_status" => "invalid",
    "redirect_url" => "",
);

// Form validation
$emailAddress = strtolower ( trim ( filter_input ( INPUT_POST, "emailAddress", FILTER_SANITIZE_EMAIL ) ) );

if ( !strlen ( $emailAddress ) ) {
    $rs [ "error" ] = "Please enter your account email address";
}

// Request password reset
if ( !strlen ( $rs [ "error" ] ) ) {
    $userAccount = User::loadUserByEmailAddress ( $emailAddress );

    if ( $userAccount ) {
        $resetHash = User::createPasswordResetHash ( $userAccount->usr_id );

        $subject = "Password reset instructions for account on " . CONFIG_SITE_NAME;
        
        $replacements = array(
            "TITLE"         => $userAccount->usr_title,
            "FIRST_NAME"    => $userAccount->usr_firstname,
            "LAST_NAME"     => $userAccount->usr_lastname,
            "SITE_NAME"     => CONFIG_SITE_NAME,
            "WEB_ROOT"      => WEB_ROOT,
            "USERNAME"      => $userAccount->usr_username,
            "ACCOUNT_ID"    => $userAccount->usr_id,
            "RESET_HASH"    => $resetHash
        );
        $message = "Dear [[[TITLE]]]. [[[FIRST_NAME]]] [[[LAST_NAME]]],<br/><br/>";
        $message .= "We've received a request to reset your password on [[[SITE_NAME]]] for account [[[USERNAME]]].<br/><br/>";
        $message .= "Follow the url below to set a new account password:<br/>";
        $message .= "<a href='[[[WEB_ROOT]]]/forgot_password_reset.html?u=[[[ACCOUNT_ID]]]&h=[[[RESET_HASH]]]'>[[[WEB_ROOT]]]/forgot_password_reset.html?u=[[[ACCOUNT_ID]]]&h=[[[RESET_HASH]]]</a><br/><br/>";
        $message .= "If you didn't request a password reset, just ignore this email and your existing password will continue to work.<br/><br/>";
        $message .= "Regards,<br/>";
        $message .= "[[[SITE_NAME]]] Admin";
        $htmlMessage = Functions::stringReplace ( $message, $replacements );

        try {
            Functions::sendEmail ( $emailAddress, $subject, $htmlMessage );

            $rs [ "redirect_url" ] = WEB_ROOT . "/forgot_password.html?s=1&emailAddress=" . urlencode ( $emailAddress );
            $rs [ "forgot_password_status" ] = "success";
        } catch ( APPException $ex ) {
            $ex->log ( );

            $rs [ "error" ] = "The password recovery email could not be sent";
        }
    } else {
        $rs [ "error" ] = "No account found with that email address";
    }
}

echo json_encode ( $rs );
