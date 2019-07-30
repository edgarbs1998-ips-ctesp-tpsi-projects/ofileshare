DELIMITER //
DROP PROCEDURE IF EXISTS sp_download_token_add //
CREATE PROCEDURE sp_download_token_add (
    IN file_id BIGINT ( 20 ) UNSIGNED,
    IN user_id BIGINT ( 20 ) UNSIGNED,
    IN user_ip VARCHAR ( 39 ),
    IN token CHAR ( 64 ),
    IN expire_date DATETIME
)
BEGIN
    INSERT INTO download_token (
        dto_fil_id,
        dto_usr_id,
        dto_token,
        dto_user_ip,
        dto_expire_date
    )
    VALUES (
        file_id,
        user_id,
        token,
        INET6_ATON(user_ip),
        expire_date
    );
	
	SELECT LAST_INSERT_ID ( );
END //
DELIMITER ;
