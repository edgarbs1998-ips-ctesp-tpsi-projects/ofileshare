DELIMITER //
DROP PROCEDURE IF EXISTS sp_db_session_read //
CREATE PROCEDURE sp_db_session_read (
	IN id VARCHAR ( 255 )
)
BEGIN
	DECLARE data TEXT;
	
	SELECT
		ses_data INTO data
	FROM session
	WHERE ses_id = id;

	IF ( data IS NOT NULL ) THEN
		SELECT data;
	END IF;
END //
DELIMITER ;
