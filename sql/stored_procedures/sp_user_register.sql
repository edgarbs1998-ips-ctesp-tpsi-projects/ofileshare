DELIMITER //
DROP PROCEDURE IF EXISTS sp_user_register //
CREATE PROCEDURE sp_user_register (
    IN title VARCHAR ( 12 ),
    IN firstname VARCHAR ( 255 ),
    IN lastname VARCHAR ( 255 ),
    IN username VARCHAR ( 64 ),
    IN password VARCHAR ( 60 ),
    IN email VARCHAR ( 255 ),
    IN identifier CHAR ( 32 ),
    IN creation_ip VARCHAR ( 39 )
)
BEGIN
    INSERT INTO user (
        usr_title,
        usr_firstname,
        usr_lastname,
        usr_username,
        usr_password,
        usr_email,
        usr_identifier,
        usr_creation_ip
    )
    VALUES (
        title,
        firstname,
        lastname,
        username,
        password,
        email,
        identifier,
        INET6_ATON(creation_ip)
    );
	
	SELECT LAST_INSERT_ID ( );
END //
DELIMITER ;
