<?php

class DBSession {
    static function register ( ) {
        ini_set ( "session.save_handler", "user" );
        session_set_save_handler (
            array ( "DBSession", "open" ),
            array ( "DBSession", "close" ),
            array ( "DBSession", "read" ),
            array ( "DBSession", "write" ),
            array ( "DBSession", "destroy" ),
            array ( "DBSession", "gc" )
        );

        // Required for PHP versions prior to 5.4.0
        register_shutdown_function ( "session_write_close" );
    }

    static function open ( ) {
        $db = Database::getInstance ( );
        return $db->isOpen ( );
    }

    static function close ( ) {
        return true;
    }

    static function read ( $sessionId ) {
        $db = Database::getInstance ( );

        try {
            $db->query ( "CALL sp_db_session_read ( :id )", array ( "id" => $sessionId ) );
        } catch ( APPException $ex ) {
            return "";
        }

        return $db->hasRows ( ) ? $db->getValue ( ) : "";
    }

    static function write ( $sessionId, $data ) {
        $db = Database::getInstance ( );
        
        try {
            $db->query ( "CALL sp_db_session_write ( :id, :data, :updated_on )", array ( "id" => $sessionId, "data" => $data, "updated_on" => time ( ) ) );
        } catch ( APPException $ex ) {
            $ex->log ( );
            return false;
        }

        return true;
    }

    static function destroy ( $sessionId ) {
        $db = Database::getInstance ( );

        try {
            $db->query ( "CALL sp_db_session_destroy ( :id )", array ( "id" => $sessionId ) );
        } catch ( APPException $ex ) {
            return false;
        }
        
        if ( !$db->hasRows ( ) ) {
            return false;
        }

        return true;
    }

    static function gc ( $lifetime ) {
        $db = Database::getInstance ( );
        $db->query ( "CALL sp_db_session_gc ( :updated_on )", array ( "updated_on" => time ( ) - $lifetime ) );

        return true;
    }
}
