DELIMITER //
DROP PROCEDURE IF EXISTS sp_file_duplicate_name //
CREATE PROCEDURE sp_file_duplicate_name (
    IN user_id BIGINT ( 20 ) UNSIGNED,
    IN folder_id BIGINT ( 20 ) UNSIGNED,
    IN file_name VARCHAR ( 260 ),
	IN file_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
	DECLARE folder_root_id BIGINT ( 20 ) UNSIGNED DEFAULT folder_id;
	
	IF folder_id IS NULL THEN
		SELECT
		 	fol_id INTO folder_root_id
    	FROM folder
    	WHERE fol_usr_id = user_id
    	AND fol_parent IS NULL;
	END IF;

	IF file_id IS NULL THEN
		SELECT
			COUNT(*)
		FROM file
		INNER JOIN folder ON fol_id = fil_fol_id
		WHERE fil_name = file_name
		AND fil_trash IS NULL
		AND fol_usr_id = user_id
		AND fol_id = folder_root_id;
	ELSE
		SELECT
			COUNT(*)
		FROM file
		INNER JOIN folder ON fol_id = fil_fol_id
		WHERE fil_name = file_name
		AND fil_trash IS NULL
		AND fol_usr_id = user_id
		AND fol_id = folder_root_id
		AND fil_id = file_id;
	END IF;
END //
DELIMITER ;
