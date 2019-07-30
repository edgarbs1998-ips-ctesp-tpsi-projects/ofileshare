DELIMITER //
DROP PROCEDURE IF EXISTS sp_user_update_password //
CREATE PROCEDURE sp_user_update_password (
	IN user_id BIGINT ( 20 ) UNSIGNED,
    IN user_ip VARCHAR ( 39 ),
    IN password VARCHAR ( 60 )
)
BEGIN
    UPDATE user
    SET
        usr_password = password,
        usr_password_change_date = NOW(),
        usr_password_change_ip = INET6_ATON(user_ip),
        usr_password_reset_hash = NULL
    WHERE usr_id = user_id;
END //
DELIMITER ;
