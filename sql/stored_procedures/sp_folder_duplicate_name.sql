DELIMITER //
DROP PROCEDURE IF EXISTS sp_folder_duplicate_name //
CREATE PROCEDURE sp_folder_duplicate_name (
    IN user_id BIGINT ( 20 ) UNSIGNED,
    IN folder_parent BIGINT ( 20 ) UNSIGNED,
    IN folder_name VARCHAR ( 260 ),
	IN folder_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
	DECLARE folder_root_id BIGINT ( 20 ) UNSIGNED DEFAULT folder_parent;
	
	IF folder_parent IS NULL THEN
		SELECT
		 	fol_id INTO folder_root_id
    	FROM folder
    	WHERE fol_usr_id = user_id
    	AND fol_parent IS NULL;
	END IF;

	IF folder_id IS NULL THEN
		SELECT
			fol_id
		FROM folder
		WHERE fol_name = folder_name
		AND fol_parent = folder_root_id
		AND fol_usr_id = user_id;
	ELSE
		SELECT
			fol_id
		FROM folder
		WHERE fol_name = folder_name
		AND fol_parent = folder_root_id
		AND fol_usr_id = user_id
		AND fol_id != folder_id;
	END IF;
END //
DELIMITER ;
