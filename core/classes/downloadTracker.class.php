<?php

class DownloadTracker {
    public $errorMsg = null;
    public $file = null;
    public $id = null;

    function __construct ( File $file ) {
        $this->errorMsg = null;
        $this->file = $file;
    }

    function create ( $startOffset = 0, $seekEnd = -1 ) {
        $db = Database::getInstance ( );
        $user = User::getInstance ( );

        $downloadUserId = null;
        if ( $user->isLogged ( ) ) {
            $downloadUserId = $user->username;
        }

        $db->query ( "CALL sp_download_tracker_add ( :file_id, :user_id, :ip, :status, :start_offset, :seek_end )", array ( "file_id" => ( int ) $this->file->fil_id, "user_id" => ( int ) $user->id, "ip" => Functions::getUserIpAddress ( ), "status" => "downloading", "start_offset" => $startOffset, "seek_end" => $seekEnd ) );
        $this->id = $db->insertId ( );
        $db->close ( );
        
        return $this->id;
    }

    function update ( ) {
        $db = Database::getInstance ( );
        $rs = $db->query ( "UPDATE download_tracker SET dtr_updated_date = NOW(), dtr_status = 'downloading' WHERE dtr_id = :id", array ( "id" => ( int ) $this->id ) );
        $db->close ( );
    
        return $rs;
    }

    function finish ( ) {
        $db = Database::getInstance ( );
        $rs = $db->query ( "UPDATE download_tracker SET dtr_updated_date = NOW(), dtr_finished_date = NOW(), dtr_status = 'finished' WHERE dtr_id = :id", array ( "id" => ( int ) $this->id ) );
        $db->close();
        
        return $rs;
    }

    static function clearTimedOutDownloads ( ) {
        $db = Database::getInstance ( );
        $db->query ( "UPDATE download_tracker SET dtr_finished_date = NOW(), dtr_status = 'cancelled' WHERE dtr_status = 'downloading' AND dtr_updated_date < DATE_SUB(NOW(), INTERVAL :frequency second)", array ( "frequency" => ( int ) DOWNLOAD_TRACKER_UPDATE_FREQUENCY ) );
        $db->close ( );
    }
    
    static function purgeDownloadData ( ) {
        $db = Database::getInstance ( );
        $db->query ( "DELETE FROM download_tracker WHERE dtr_started_date < DATE_SUB(NOW(), INTERVAL :period day)", array ( "period" => ( int ) DOWNLOAD_TRACKER_PURGE_PERIOD ) );
        $db->close ( );
    }
}
