DELIMITER //
DROP PROCEDURE IF EXISTS sp_folder_delete //
CREATE PROCEDURE sp_folder_delete (
	IN folder_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
    DELETE FROM folder
    WHERE fol_id = folder_id;
END //
DELIMITER ;
