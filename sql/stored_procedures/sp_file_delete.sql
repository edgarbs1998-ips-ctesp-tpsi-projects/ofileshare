DELIMITER //
DROP PROCEDURE IF EXISTS sp_file_delete //
CREATE PROCEDURE sp_file_delete (
	IN file_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
    DELETE FROM file_tag
    WHERE fta_fil_id = file_id;

    DELETE FROM file
    WHERE fil_id = file_id;
END //
DELIMITER ;
