<?php

class User {
    // Singleton object
    private static $me;

    private $id;
    private $identifier;
    private $username;
    private $title;
    private $firstname;
    private $lastname;
    private $level;
    private $levelName;

    private function __construct ( ) {
        $this->id           = null;
        $this->identifier   = null;
        $this->username     = null;
        $this->title        = null;
        $this->firstname    = null;
        $this->lastname     = null;
        $this->level        = 0;
        $this->levelName    = self::getLevelName ( $this->level );

        if ( $this->sessionLogin ( ) ) {
            return;
        }
    }

    // Get Singleton object
    static function getInstance ( ) {
        if ( is_null ( self::$me ) ) {
            self::$me = new User ( );
        }
        return self::$me;
    }

    function __get ( $key ) {
        return $this->$key;
    }

    static function register ( $username, $password, $email, $title, $firstname, $lastname ) {
        $db = Database::getInstance ( );
        $userIdentifier = MD5 ( time ( ) . $username . $email );

        try {
            $db->query ( "CALL sp_user_register (   :title,
                                                    :firstname,
                                                    :lastname,
                                                    :username,
                                                    :password,
                                                    :email,
                                                    :identifier,
                                                    :creation_ip )",
                        array (
                            "title"         => $title,
                            "firstname"     => $firstname,
                            "lastname"      => $lastname,
                            "username"      => $username,
                            "password"      => password_hash ( $password, PASSWORD_BCRYPT  ),
                            "email"         => $email,
                            "identifier"    => $userIdentifier,
                            "creation_ip"   => Functions::getUserIpAddress ( ),
                        )
            );

            $db->query ( "CALL sp_folder_add (  :user_id,
                                                :parent,
                                                :name )",
                        array (
                            "user_id"   => $db->insertId ( ),
                            "parent"    => null,
                            "name"      => "ROOT - " . $userIdentifier,
                        )
            );
        } catch ( APPException $ex ) {
            $ex->log ( );
            return false;
        }

        if ( $db->hasRows ( ) ) {
            return true;
        }

        return false;
    }

    function isLogged ( ) {
        return $this->level > 0;
    }

    private function sessionLogin ( ) {
        if ( isset ( $_SESSION [ "user" ] ) ) {
            $sessionUser = unserialize ( $_SESSION [ "user" ] );
            if ( is_object ( $sessionUser ) ) {
                foreach ( $sessionUser AS $key => $value ) {
                    $this->$key = $value;
                }

                return true;
            }
        }
        
        return false;
    }

    function login ( $username, $rawPassword ) {
        $db = Database::getInstance ( );

        $db->query ( "CALL sp_user_load_by_username ( :username )", array ( "username" => $username ) );
        $row = $db->getRow ( );
        if ( !$row ) {
            return false;
        }

        if ( !password_verify ( $rawPassword, $row->usr_password ) ) {
            self::logFailedLoginAttempt ( Functions::getUserIpAddress ( ), $row->usr_id );

            return false;
        }

        $this->id           = $row->usr_id;
        $this->identifier   = $row->usr_identifier;
        $this->username     = $row->usr_username;
        $this->title        = $row->usr_title;
        $this->firstname    = $row->usr_firstname;
        $this->lastname     = $row->usr_lastname;
        $this->level        = $row->usr_ule_level;
        $this->levelName    = self::getLevelName ( $this->level );

        foreach ( $row AS $key => $value ) {
            $this->$key = $value;
        }

        self::logSuccessfullLogin ( Functions::getUserIpAddress ( ), $this->id );

        self::purgeOldSessionData ( );

        $this->storeSessionData ( );

        return true;
    }

    function storeSessionData ( ) {
        $_SESSION [ "user" ] = serialize ( $this );
    }

    public function logout ( ) {
        $this->id           = null;
        $this->identifier   = null;
        $this->username     = null;
        $this->title        = null;
        $this->firstname    = null;
        $this->lastname     = null;
        $this->level        = 0;
        $this->levelName    = self::getLevelName ( $this->level );

        session_unset ( );
        session_destroy ( );
        setcookie ( CONFIG_SESSION_NAME, "", time ( ) - 3600, "/", CONFIG_SITE_HOST_URL );
    }

    function updateUserData ( ) {
        $db = Database::getInstance ( );

        $db->query ( "CALL sp_user_load_by_username ( :username )", array ( "username" => $this->username ) );
        $row = $db->getRow ( );
        if ( !$row ) {
            return false;
        }

        $this->id           = $row->usr_id;
        $this->identifier   = $row->usr_identifier;
        $this->username     = $row->usr_username;
        $this->title        = $row->usr_title;
        $this->firstname    = $row->usr_firstname;
        $this->lastname     = $row->usr_lastname;
        $this->level        = $row->usr_ule_level;
        $this->levelName    = self::getLevelName ( $this->level );

        foreach ( $row AS $key => $value ) {
            $this->$key = $value;
        }

        $this->storeSessionData ( );

        return true;
    }

    function getAccountScreenName ( ) {
        $name = strlen ( $this->firstname ) ? ucwords ( $this->firstname ) : $this->username;
        if ( strlen ( $name ) > 15 ) {
            $name = substr ( $name, 0, 12 ) . "...";
        }

        return $name;
    }

    public function requireUser ( $redirectUrl )
    {
        $this->requireAccessLevel ( 1, $redirectUrl );
    }

    public function requireAccessLevel ( $minRequiredLevel = 0, $redirectOnFailure = null ) {
		switch ( $minRequiredLevel ) {
			case 1:
				if ( in_array ( $this->levelName, array ( "User", "Admin" ) ) ) {
					return true;
				}
				break;
			case 20:
				if ( in_array ( $this->levelName, array ( "Admin" ) ) ) {
					return true;
				}
		}

        if ( !is_null ( $redirectOnFailure ) ) {
            Functions::redirect ( $redirectOnFailure );
        }

        return false;
    }

    static function createPasswordResetHash ( $userId ) {
        do {
            $hash = md5 ( microtime ( ) . $userId );

            $userAccount = self::loadUserByPasswordResetHash ( $hash );
        } while ( $userAccount == true );

        $db = Database::getInstance ( );
        $db->query ( "CALL sp_user_update_password_reset_hash ( :id, :hash )", array ( "id" => $userId, "hash" => $hash ) );

        return $hash;
    }

    static function purgeOldSessionData ( ) {
        $db = Database::getInstance ( );
        $db->query ( "CALL sp_db_session_gc ( :updated_on )", array ( "updated_on" => time ( ) - ( 60 * 60 * 24 * 2 ) ) ); // 2 days
    }

    static function logFailedLoginAttempt ( $ipAddress, $userId ) {
        $db = Database::getInstance ( );

        $db->query ( "CALL sp_user_logon_add ( :user_id, :ip_address, :error, :error_message )", array ( "user_id" => $userId, "ip_address" => $ipAddress, "error" => 1, "error_message" => "Password does not match" ) );
        if ( !$db->hasRows ( ) ) {
            return false;
        }

        return true;
    }

    static function logSuccessfullLogin ( $ipAddress, $userId ) {
        $db = Database::getInstance ( );

        $db->query ( "CALL sp_user_logon_add ( :user_id, :ip_address, -1, null )", array ( "user_id" => $userId, "ip_address" => $ipAddress ) );
        if ( !$db->hasRows ( ) ) {
            return false;
        }

        return true;
    }

    static function loadUserById ( $userId ) {
        $db = Database::getInstance ( );

        $db->query ( "CALL sp_user_load_by_id ( :user_id )", array ( "user_id" => $userId ) );
        $row = $db->getRow ( );
        if ( !$row ) {
            return false;
        }

        $user = new User ( );
        foreach ( $row AS $key => $value ) {
            $user->$key = $value;
        }

        return $user;
    }

    static function loadUserByUsername ( $username ) {
        $db = Database::getInstance ( );

        $db->query ( "CALL sp_user_load_by_username ( :username )", array ( "username" => $username ) );
        $row = $db->getRow ( );
        if ( !$row ) {
            return false;
        }

        $user = new User ( );
        foreach ( $row AS $key => $value ) {
            $user->$key = $value;
        }

        return $user;
    }

    static function loadUserByEmailAddress ( $emailAddress ) {
        $db = Database::getInstance ( );

        $db->query ( "CALL sp_user_load_by_email ( :email )", array ( "email" => $emailAddress ) );
        $row = $db->getRow ( );
        if ( !$row ) {
            return false;
        }

        $user = new User ( );
        foreach ( $row AS $key => $value ) {
            $user->$key = $value;
        }

        return $user;
    }

    static function loadUserByPasswordResetHash ( $hash ) {
        $db = Database::getInstance ( );

        $db->query ( "CALL sp_user_load_by_password_reset_hash ( :hash )", array ( "hash" => $hash ) );
        $row = $db->getRow ( );
        if ( !$row ) {
            return false;
        }

        $user = new User ( );
        foreach ( $row AS $key => $value ) {
            $user->$key = $value;
        }

        return $user;
    }

    static function getMaxFileStorage ( $userId )
    {
        $userAccount = self::loadUserById ( $userId );

        $limit = self::getUserLevelValue ( "ule_max_storage", $userAccount->usr_ule_level );
        $limit = ( !strlen ( $limit ) || !$limit ) ? null : $limit;

        return $limit;
    }

    static function getMaxUploadFilesize ( $userId )
    {
        $userAccount = self::loadUserById ( $userId );

        $limit = self::getUserLevelValue ( "ule_max_upload_size", $userAccount->usr_ule_level );
        $limit = ( !strlen ( $limit ) || !$limit ) ? null : $limit;

        return $limit;
    }

    static function getAvailableFileStorage ( $userId ) {
        $maxFileStorage = self::getMaxFileStorage ( $userId );
        if ( $maxFileStorage === null ) {
            return null;
        }

        $totalUsed = self::getTotalActiveFileSizeByUser ( $userId );
        if ( $totalUsed > $maxFileStorage ) {
            return 0;
        }

        return $maxFileStorage - $totalUsed;
    }

    static function getTotalActiveFileSizeByUser ( $userId ) {
        $db = Database::getInstance ( );
        $db->query ( "CALL sp_user_total_file_size ( :user_id)", array ( "user_id" => $userId ) );

        return $db->getValue( );
    }

    static function getLevelName ( $levelId ) {
        $db = Database::getInstance ( );

        if ( $levelId == 0 ) {
            return "Unregistered";
        }

        $db->query ( "CALL sp_user_level_name ( :level_id )", array ( "level_id" => $levelId ) );
        if ( !$db->hasRows ( ) ) {
            return "Unregistered";
        }

        return $db->getValue ( );
    }

	static function getUserLevelValue ( $column, $level ) {
        $db = Database::getInstance ( );
        $db->query ( "SELECT " . $column . " FROM user_level WHERE ule_level = :level_id", array ( "level_id" => $level ) );
        if ( !$db->hasRows ( ) ) {
            return null;
        }

        return $db->getValue ( );
    }

    static function getUsernameById ( $userId ) {
        $db = Database::getInstance ( );
        $db->query ( "SELECT usr_username FROM user WHERE usr_id = :user_id", array ( "user_id" => ( int ) $userId ) );

        return $db->getValue ( );
    }

    static function getLevelBYUserId ( $userId ) {
        $db = Database::getInstance ( );
        $db->query ( "SELECT usr_ule_level FROM user WHERE usr_id = :user_id", array ( "user_id" => $userId ) );
        
        return $db->getValue ( );
    }
}
