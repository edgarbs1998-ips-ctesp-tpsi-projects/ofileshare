DELIMITER //
DROP PROCEDURE IF EXISTS sp_download_tracker_add //
CREATE PROCEDURE sp_download_tracker_add (
    IN file_id BIGINT ( 20 ) UNSIGNED,
    IN user_id BIGINT ( 20 ) UNSIGNED,
    IN ip VARCHAR ( 39 ),
    IN status ENUM ( "downloading", "finished", "error", "cancelled" ),
    IN start_offset BIGINT ( 20 ),
    IN seek_end BIGINT ( 20 )
)
BEGIN
    INSERT INTO download_tracker (
        dtr_fil_id,
        dtr_usr_id,
        dtr_ip,
        dtr_status,
        dtr_start_offset,
        dtr_seek_end
    )
    VALUES (
        file_id,
        user_id,
        INET6_ATON(ip),
        status,
        start_offset,
        seek_end
    );
	
	SELECT LAST_INSERT_ID ( );
END //
DELIMITER ;
