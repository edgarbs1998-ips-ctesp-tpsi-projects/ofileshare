DELIMITER //
DROP PROCEDURE IF EXISTS sp_folder_load_by_user //
CREATE PROCEDURE sp_folder_load_by_user (
    IN user_id BIGINT ( 20 ) UNSIGNED,
    IN return_root BOOLEAN
)
BEGIN
	IF return_root = TRUE THEN
        SELECT
            fol_id,
            fol_usr_id,
            fol_parent,
            fol_name
        FROM folder
        WHERE fol_usr_id = user_id
        GROUP BY fol_name ASC;
    ELSE
        SELECT
            fol_id,
            fol_usr_id,
            fol_parent,
            fol_name
        FROM folder
        WHERE fol_usr_id = user_id
        AND fol_parent IS NOT NULL
        GROUP BY fol_name ASC;
    END IF;
END //
DELIMITER ;
