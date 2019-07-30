DELIMITER //
DROP PROCEDURE IF EXISTS sp_file_update_shorturl //
CREATE PROCEDURE sp_file_update_shorturl (
    IN file_id BIGINT ( 20 ) UNSIGNED,
    IN file_shorturl CHAR ( 16 )
)
BEGIN
    UPDATE file
    SET
        fil_shorturl = file_shorturl
    WHERE fil_id = file_id;
END //
DELIMITER ;
