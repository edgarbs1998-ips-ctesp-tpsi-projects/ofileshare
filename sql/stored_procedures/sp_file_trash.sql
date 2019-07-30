DELIMITER //
DROP PROCEDURE IF EXISTS sp_file_trash //
CREATE PROCEDURE sp_file_trash (
    IN file_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
    UPDATE file
    SET
        fil_trash = NOW(),
        fil_hash = NULL
    WHERE fil_id = file_id;
END //
DELIMITER ;
