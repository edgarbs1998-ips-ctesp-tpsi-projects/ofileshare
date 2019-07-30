DELIMITER //
DROP PROCEDURE IF EXISTS sp_download_token_load //
CREATE PROCEDURE sp_download_token_load (
    IN token CHAR ( 64 ),
    IN file_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
    SELECT
        dto_id,
        dto_fil_id,
        dto_usr_id,
        dto_token,
        dto_user_ip,
        dto_create_date,
        dto_expire_date
    FROM download_token
    WHERE dto_fil_id = file_id
    AND dto_token = token;
END //
DELIMITER ;
