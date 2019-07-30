<?php

class Notification {
    private static $pageErrors = array ( );
    private static $pageSuccess = array ( );

    static function hasErrors ( ) {
        if ( !count ( self::$pageErrors ) ) {
            return false;
        }

        return true;
    }

    static function setError ( $message ) {
        self::$pageErrors [ ] = $message;
    }

    static function getErrors ( ) {
        return self::$pageErrors;
    }

    static function outputErrors ( ) {
        $errors = self::getErrors ( );
        if ( count ( $errors ) ) {
            foreach ( $errors AS $message ) {
                $htmlArr [ ] = "<li class='no-side-margin'><i class='fa fa-exclamation-triangle margin-right-20'></i>&nbsp;" . $message . "</li>";
            }

            return "<ul class='pageErrorClass'>" . implode ( "", $htmlArr ) . "</ul>";
        }
    }

    static function hasSuccess ( ) {
        if ( !count ( self::$pageSuccess ) ) {
            return false;
        }

        return true;
    }

    static function setSuccess ( $message ) {
        self::$pageSuccess [ ] = $message;
    }

    static function getSuccess ( ) {
        return self::$pageSuccess;
    }

    static function outputSuccess ( ) {
        $success = self::getErrors ( );
        if ( count ( $success ) ) {
            foreach ( $success AS $message ) {
                $htmlArr [ ] = "<li class='no-side-margin'><i class='fa fa-check-square margin-right-20'></i>&nbsp;" . $message . "</li>";
            }

            return "<ul class='pageSuccess'>" . implode ( "", $htmlArr ) . "</ul>";
        }
    }
}
