DELIMITER //
DROP PROCEDURE IF EXISTS sp_db_session_gc //
CREATE PROCEDURE sp_db_session_gc (
	IN updated_on INT ( 11 )
)
BEGIN
    DELETE FROM session
    WHERE ses_updated_on < updated_on;
END //
DELIMITER ;
