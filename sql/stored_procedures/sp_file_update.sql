DELIMITER //
DROP PROCEDURE IF EXISTS sp_file_update //
CREATE PROCEDURE sp_file_update (
    IN file_id BIGINT ( 20 ) UNSIGNED,
    IN file_name VARCHAR ( 260 ),
    IN folder_id BIGINT ( 20 ) UNSIGNED,
    IN file_privacy BIGINT ( 20 ) UNSIGNED,
    IN user_id BIGINT ( 20 ) UNSIGNED
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

    UPDATE file
    SET
        fil_name = file_name,
        fil_fol_id = folder_root_id,
        fil_fpe_id = file_privacy
    WHERE fil_id = file_id;
END //
DELIMITER ;
