DELIMITER //
DROP PROCEDURE IF EXISTS sp_folder_update //
CREATE PROCEDURE sp_folder_update (
    IN user_id BIGINT ( 20 ) UNSIGNED,
    IN folder_id BIGINT ( 20 ) UNSIGNED,
    IN parent BIGINT ( 20 ) UNSIGNED,
    IN name VARCHAR ( 260 )
)
BEGIN
    DECLARE parent_root BIGINT ( 20 ) UNSIGNED DEFAULT parent;

    IF parent IS NULL THEN
		SELECT
		 	fol_id INTO parent_root
    	FROM folder
    	WHERE fol_usr_id = user_id
    	AND fol_parent IS NULL;
	END IF;

    UPDATE folder
    SET
        fol_parent = parent_root,
        fol_name = name
    WHERE fol_id = folder_id;
END //
DELIMITER ;
