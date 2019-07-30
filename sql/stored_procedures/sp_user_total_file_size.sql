DELIMITER //
DROP PROCEDURE IF EXISTS sp_user_total_file_size //
CREATE PROCEDURE sp_user_total_file_size (
    IN user_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
	SELECT
		SUM(fil_size)
	FROM file
	INNER JOIN folder ON fol_id = fil_fol_id
	WHERE fol_usr_id = user_id
    AND fil_trash IS NULL;
END //
DELIMITER ;
