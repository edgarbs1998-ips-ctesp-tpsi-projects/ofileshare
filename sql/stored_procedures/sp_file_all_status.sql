DELIMITER //
DROP PROCEDURE IF EXISTS sp_file_all_status //
CREATE PROCEDURE sp_file_all_status (
    IN user_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
    SELECT
        COUNT(*) AS total,
        SUM(fil_size) AS total_size,
        IF(fil_trash IS NULL,"active","trashed") AS status
    FROM file
    INNER JOIN folder ON fol_id = fil_fol_id
    WHERE fol_usr_id = user_id
    GROUP BY IF(fil_trash IS NULL,"active","trashed");
END //
DELIMITER ;
