<?php

class Database {

    // Singleton object
    private static $me;

    private $db;
    private $result;

    private function __construct ( ) {
        $this->db = null;
        $this->result = null;

        $this->open ( );
    }

    // Get Singleton object
    static function getInstance ( ) {
        if ( is_null ( self::$me ) ) {
            self::$me = new Database ( );
        }
        return self::$me;
    }

    function open ( ) {
        if ( !$this->checkPDODriver ( CONFIG_DB_DRIVER ) ) {
            throw new APPException ( "Database driver unavailable." );
        }

        try {
            $this->db = new PDO (
                CONFIG_DB_DRIVER .
                    ":host=" . CONFIG_DB_HOST .
                    ";port=" . CONFIG_DB_PORT .
                    ";dbname=" . CONFIG_DB_NAME .
                    ";charset=utf8",
                CONFIG_DB_USER,
                CONFIG_DB_PASS,
                array (
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                )
            );
        }
        catch ( PDOException $ex ) {
            throw new APPException ( $ex );
        }

        if ( $this->isOpen ( ) ) {
            // Required for PHP versions prior to 5.3.6
            $this->db->exec ( "SET NAMES utf8 COLLATE utf8_general_ci" );
        }
    }

    function isOpen ( ) {
        return is_object ( $this->db );
    }

    function close ( ) {
        $this->result->closeCursor ( );
        $this->result = null;
        self::closeDB ( );
    }

    static function closeDB ( ) {
        if ( !is_null ( self::$me ) ) {
            self::$me->db = null;
            self::$me = null;
        }
    }

    function query ( $query, $args = null ) {
        if ( !$this->isOpen ( ) ) {
            $this->open ( );
        }

        if ( is_object ( $this->result ) ) {
            $this->result->closeCursor ( );
            $this->result = null;
        }

        try {
            $stmt = $this->db->prepare ( $query );
            if ( is_array ( $args ) ) {
                foreach ( $args AS $key => $val ) {
                    $arg = $this->prepareArg ( $val );
                    $stmt->bindValue ( ":" . $key, $arg [ "value" ], $arg [ "type" ] );
                }
            }
            $stmt->execute ( );
        } catch ( PDOException $ex ) {
            throw new APPException ( $ex );
        }

        $this->result = $stmt;
    }

    function insertId ( ) {
        if ( !$this->isOpen ( ) ) {
            return false;
        }

        if ( !is_object ( $this->result ) || $this->result == false ) {
            return false;
        }

        // Does not work with Stored Procedures ( PDO MySQL Limitation )
        //return $this->db->lastInsertId ( );

        return $this->getValue ( );
    }

    function numRows ( ) {
        if ( !is_object ( $this->result ) || $this->result == false ) {
            return false;
        }

        return $this->result->rowCount ( );
    }

    function hasRows ( ) {
        if ( $this->numRows ( ) <= 0 ) {
            return false;
        }

        return true;
    }

    function getValue ( ) {
        if ( !is_object ( $this->result ) || $this->result == false ) {
            return false;
        }

        $row = $this->result->fetch ( PDO::FETCH_NUM );
        if ( $row == false ) {
            return false;
        }

        return $row [ 0 ];
    }

    function getRow ( ) {
        if ( !is_object ( $this->result ) || $this->result == false ) {
            return false;
        }

        $row = $this->result->fetch ( PDO::FETCH_OBJ );
        if ( $row == false ) {
            return false;
        }

        return $row;
    }

    function getRows ( ) {
        if ( !is_object ( $this->result ) || $this->result == false ) {
            return false;
        }

        $rows = $this->result->fetchAll ( PDO::FETCH_OBJ );
        if ( $rows == false ) {
            return false;
        }

        return $rows;
    }

    function escape ( $var ) {
        if ( !$this->isOpen ( ) ) {
            $this->open ( );
        }

        $var = $this->db->quote ( $var );
        if ( strlen ( $var ) > 2 ) {
            $var = substr ( $var, 1, strlen ( $var ) - 2 );
        }

        return $var;
    }

    private function checkPDODriver ( $driver ) {
        if ( !class_exists ( "PDO", false ) || !extension_loaded ( "pdo_" . $driver ) ) {
            return false;
        }

        return true;
    }

    private function prepareArg ( $arg ) {
        switch ( gettype ( $arg ) ) {
            case "boolean":
                return array ( "value" => $arg, "type" => PDO::PARAM_BOOL );
            case "NULL":
                return array ( "value" => $arg, "type" => PDO::PARAM_NULL );
            case "integer":
                return array ( "value" => $arg, "type" => PDO::PARAM_INT );
            default:
                return array ( "value" => strval ( $arg ), "type" => PDO::PARAM_STR );
        }
    }
}
