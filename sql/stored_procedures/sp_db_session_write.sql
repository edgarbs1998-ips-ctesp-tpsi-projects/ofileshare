DELIMITER //
DROP PROCEDURE IF EXISTS sp_db_session_write //
CREATE PROCEDURE sp_db_session_write (
	IN id VARCHAR ( 255 ),
    IN data TEXT,
    IN updated_on INT
)
BEGIN
    INSERT INTO session (
        ses_id,
        ses_data,
        ses_updated_on
    )
    VALUES (
        id,
        data,
        updated_on
    )
    ON DUPLICATE KEY UPDATE
        ses_data = data,
        ses_updated_on = updated_on;
	
	SELECT LAST_INSERT_ID ( );
END //
DELIMITER ;
