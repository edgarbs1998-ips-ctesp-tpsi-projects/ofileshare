DELIMITER //
DROP PROCEDURE IF EXISTS sp_user_update //
CREATE PROCEDURE sp_user_update (
    IN user_id BIGINT ( 20 ) UNSIGNED,
    IN title VARCHAR ( 12 ),
    IN firstname VARCHAR ( 255 ),
    IN lastname VARCHAR ( 255 ),
    IN email VARCHAR ( 255 )
)
BEGIN
    UPDATE user
    SET
        usr_title = title,
        usr_firstname = firstname,
        usr_lastname = lastname,
        usr_email = email
    WHERE usr_id = user_id;
END //
DELIMITER ;
