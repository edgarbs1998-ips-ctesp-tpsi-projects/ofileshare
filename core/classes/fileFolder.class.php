<?php

class FileFolder {
    public function getFolderUrl ( ) {
        return WEB_ROOT . "/folder/" . ( int ) $this->fol_id . "/" . $this->getSafeFoldernameForUrl ( );
    }

    public function getSafeFoldernameForUrl ( ) {
        return str_replace ( array (" ", "\"", "'", ";", "#", "%"), "_", strip_tags ( $this->fol_name ) );
    }
    
    public function remove ( ) {
        return self::deleteFolder ( $this->fol_id, $this->fol_usr_id );
    }
    
    static function deleteFolder ( $folderId, $userId ) {
        $db = Database::getInstance ( );

        $db->query ( "SELECT fol_id FROM folder WHERE fol_parent = :folder_id AND fol_usr_id = :user_id", array ( "folder_id" => ( int ) $folderId, "user_id" => ( int ) $userId ) );
        $subFolders = $db->getRows ( );
        if ( $subFolders ) {
            foreach ( $subFolders as $subFolder ) {
                self::deleteFolder ( $subFolder->fol_id, $userId );
            }
        }

        $db->query ( "CALL sp_file_folder_delete ( :folder_id, :user_id)", array ( "folder_id" => ( int ) $folderId, "user_id" => ( int ) $userId ) );
        $db->query ( "CALL sp_folder_delete ( :folder_id )", array ( "folder_id" => ( int ) $folderId ) );
    }

    static function loadById ( $folderId, $returnRoot = false ) {
        $db = Database::getInstance ( );
        $db->query ( "CALL sp_folder_load_by_id ( :folder_id, :return_root )", array ( "folder_id" => $folderId, "return_root" => $returnRoot ) );
        if ( !$db->hasRows ( ) ) {
            return false;
        }

        $row = $db->getRow ( );
        $folder = new FileFolder ( );
        foreach ( $row AS $key => $value ) {
            $folder->$key = $value;
        }

        return $folder;
    }

    static function loadAllByAccount ( $userId, $returnRoot = false ) {
        $db = Database::getInstance ( );
        $db->query ( "CALL sp_folder_load_by_user ( :user_id, :return_root )", array ( "user_id" => $userId, "return_root" => $returnRoot ) );
        if ( !$db->hasRows ( ) ) {
            return null;
        }

        return $db->getRows ( );
    }

    static function loadAllForSelect ( $userId, $delimiter = "/" ) {
        $rs = array ( );
        $folders = self::loadAllByAccount ( $userId );
        if ( $folders ) {
            $lookupArr = array ( );
            foreach ( $folders AS $folder ) {
                $lookupArr [ $folder->fol_id ] = array( "n" => $folder->fol_name, "p" => $folder->fol_parent );
            }

            foreach ( $folders AS $folder ) {
                $folderLabelArr = array ( );
                $folderLabelArr [ ] = $folder->fol_name;
                $failSafe = 0;
                $parentId = $folder->fol_parent;
                while ( $parentId != null && $failSafe < 30 ) {
                    $failSafe++;
                    if ( isset ( $lookupArr [ $parentId ] ) ) {
                        $folderLabelArr [ ] = $lookupArr [ $parentId ] [ "n" ];
                        $parentId = $lookupArr [ $parentId ] [ "p" ];
                    }
                }

                $folderLabelArr = array_reverse ( $folderLabelArr );
                $rs [ $folder->fol_id ] = implode ( $delimiter, $folderLabelArr );
            }
        }

        natcasesort ( $rs );

        return $rs;
    }

    static function hydrate ( $folderData ) {
        $folderObj = new FileFolder ( );
        foreach ( $folderData AS $key => $value ) {
            $folderObj->$key = $value;
        }

        return $folderObj;
    }
}
