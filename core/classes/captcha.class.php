<?php

class Captcha {
    static function getCaptcha ( ) {
        return  "<div class='g-recaptcha' data-sitekey='" . CONFIG_CAPTCHA_PUBLIC_KEY . "'></div>
                <script type='text/javascript' src='https://www.google.com/recaptcha/api.js'></script>";
    }

    static function checkCaptcha ( $response ) {
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $data = array (
            "secret" => CONFIG_CAPTCHA_SECRET_KEY,
            "response" => $response,
            "remoteip" => Functions::getUserIpAddress ( ),
        );

        $response = Functions::httpPost ( $url, $data );
        $json = json_decode ( $response, true );

        if ( $json [ "success" ] == "true" ) {
            return true;
        }

        return false;
    }
}
