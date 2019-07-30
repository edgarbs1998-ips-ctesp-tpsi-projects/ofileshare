DELIMITER //
DROP PROCEDURE IF EXISTS sp_folder_load_by_id //
CREATE PROCEDURE sp_folder_load_by_id (
    IN folder_id BIGINT ( 20 ) ,
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
        WHERE fol_id = folder_id
        GROUP BY fol_name ASC;
    ELSE
        SELECT
            fol_id,
            fol_usr_id,
            fol_parent,
            fol_name
        FROM folder
        WHERE fol_id = folder_id
        AND fol_parent IS NOT NULL
        GROUP BY fol_name ASC;
    END IF;
END //
DELIMITER ;
