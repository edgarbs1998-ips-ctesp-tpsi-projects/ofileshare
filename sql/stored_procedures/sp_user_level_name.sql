DELIMITER //
DROP PROCEDURE IF EXISTS sp_user_level_name //
CREATE PROCEDURE sp_user_level_name (
	IN level_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
	DECLARE data VARCHAR ( 64 );
	
	SELECT
		ule_name INTO data
	FROM user_level
	WHERE ule_level = level_id;

	IF ( data IS NOT NULL ) THEN
		SELECT data;
	END IF;
END //
DELIMITER ;
