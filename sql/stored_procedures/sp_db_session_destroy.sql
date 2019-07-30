DELIMITER //
DROP PROCEDURE IF EXISTS sp_db_session_destroy //
CREATE PROCEDURE sp_db_session_destroy (
	IN id VARCHAR ( 255 )
)
BEGIN
    DELETE FROM session
    WHERE ses_id = id;
END //
DELIMITER ;
