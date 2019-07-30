DELIMITER //
DROP PROCEDURE IF EXISTS sp_user_update_password_reset_hash //
CREATE PROCEDURE sp_user_update_password_reset_hash (
	IN user_id BIGINT ( 20 ) UNSIGNED,
    IN password_reset_hash CHAR ( 32 )
)
BEGIN
    UPDATE user
    SET
        usr_password_reset_hash = password_reset_hash
    WHERE usr_id = user_id;
END //
DELIMITER ;
