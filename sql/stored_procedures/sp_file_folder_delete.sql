DELIMITER //
DROP PROCEDURE IF EXISTS sp_file_folder_delete //
CREATE PROCEDURE sp_file_folder_delete (
    IN folder_id BIGINT ( 20 ) UNSIGNED,
    IN user_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
    DECLARE folder_root BIGINT ( 20 ) UNSIGNED;

	SELECT
		fol_id INTO folder_root
    FROM folder
    WHERE fol_usr_id = user_id
    AND fol_parent IS NULL;

    UPDATE file
    SET
        fil_fol_id = folder_root
    WHERE fil_fol_id = folder_id;
END //
DELIMITER ;
