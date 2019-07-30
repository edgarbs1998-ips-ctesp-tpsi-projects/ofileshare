DELIMITER //
DROP PROCEDURE IF EXISTS sp_user_logon_add //
CREATE PROCEDURE sp_user_logon_add (
    IN user_id BIGINT ( 20 ) UNSIGNED,
    IN ip_address VARCHAR ( 39 ),
    IN error SMALLINT ( 6 ),
    IN error_message VARCHAR ( 255 )
)
BEGIN
    INSERT INTO user_logon (
        ulo_usr_id,
        ulo_ip,
        ulo_error,
        ulo_error_message
    )
    VALUES (
        user_id,
        INET6_ATON(ip_address),
        error,
        error_message
    );
	
	SELECT LAST_INSERT_ID ( );
END //
DELIMITER ;
