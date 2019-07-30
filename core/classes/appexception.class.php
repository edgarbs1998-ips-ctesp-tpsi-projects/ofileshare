<?php

class APPException extends RuntimeException {
    public function __construct ( $message ) {
        parent::__construct ( $message );
    }

    public function log ( ) {
        if ( DEBUG_MODE ) {
            echo $this->getMessage ( );
        } else {
            error_log ( addslashes ( "[" . date ( "c" ) . "]" . PHP_EOL . $this->getMessage ( ) . PHP_EOL . PHP_EOL ), 3, CORE_ROOT . "/logs/" . basename ( $this->getFile ( ), ".php" ) . ".log" );
        }
    }

    public function display ( ) {
        if ( DEBUG_MODE ) {
            define ( "APPEXCEPTION_MESSAGE", $this->getMessage ( ) );
        } else {
            $this->log ( );

            define ( "APPEXCEPTION_MESSAGE", "Whoops, looks like something went wrong. Please try again later!" );
        }

        include_once ( CORE_ROOT . "/pages/appexception.php" );

        exit ( );
    }
}
