DELIMITER //
DROP PROCEDURE IF EXISTS sp_user_owns_folder //
CREATE PROCEDURE sp_user_owns_folder (
    IN user_id BIGINT ( 20 ) UNSIGNED,
    IN folder_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
    SELECT
        fol_id,
        fol_usr_id
    FROM folder
    WHERE fol_id = folder_id
    AND fol_usr_id = user_id
    LIMIT 1;
END //
DELIMITER ;
