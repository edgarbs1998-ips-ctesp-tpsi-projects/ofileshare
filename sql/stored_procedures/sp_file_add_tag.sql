DELIMITER //
DROP PROCEDURE IF EXISTS sp_file_add_tag //
CREATE PROCEDURE sp_file_add_tag (
    IN file_id BIGINT ( 20 ) UNSIGNED,
    IN file_tag VARCHAR ( 64 )
)
BEGIN
    DECLARE tag_id_2 BIGINT ( 20 );

    SELECT
        tag_id INTO tag_id_2
    FROM tag
    WHERE tag_name = file_tag;

    IF tag_id_2 IS NULL THEN
        INSERT INTO tag (
            tag_name
        )
        VALUES (
            file_tag
        );

        SELECT LAST_INSERT_ID ( ) INTO tag_id_2;
    END IF;

    IF ( SELECT NOT EXISTS ( SELECT 1 FROM file_tag WHERE fta_fil_id = file_id AND fta_tag_id = tag_id_2 ) ) THEN
        INSERT INTO file_tag (
            fta_fil_id,
            fta_tag_id
        )
        VALUES (
            file_id,
            tag_id_2
        );
    END IF;
END //
DELIMITER ;
