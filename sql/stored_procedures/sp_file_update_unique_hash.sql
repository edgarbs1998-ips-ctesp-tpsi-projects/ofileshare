DELIMITER //
DROP PROCEDURE IF EXISTS sp_file_update_unique_hash //
CREATE PROCEDURE sp_file_update_unique_hash (
    IN file_id BIGINT ( 20 ) UNSIGNED,
    IN file_unique_hash CHAR ( 64)
)
BEGIN
    UPDATE file
    SET
        fil_unique_hash = file_unique_hash
    WHERE fil_id = file_id;
END //
DELIMITER ;
