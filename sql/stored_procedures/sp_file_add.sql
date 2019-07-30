DELIMITER //
DROP PROCEDURE IF EXISTS sp_file_add //
CREATE PROCEDURE sp_file_add (
    IN user_id BIGINT ( 20 ) UNSIGNED,
    IN file_name VARCHAR ( 260 ),
    IN file_type VARCHAR ( 140 ),
    IN file_extension VARCHAR ( 10 ),
    IN file_size BIGINT ( 20 ) UNSIGNED,
    IN file_path VARCHAR ( 260 ),
    IN file_hash CHAR ( 32 ),
    IN delete_hash CHAR ( 32 ),
    IN folder_id BIGINT ( 20 ) UNSIGNED,
    IN upload_ip VARCHAR ( 39 )
)
BEGIN
    DECLARE folder_root_id BIGINT ( 20 ) UNSIGNED DEFAULT folder_id;

    IF folder_id IS NULL THEN
		SELECT
		 	fol_id INTO folder_root_id
    	FROM folder
    	WHERE fol_usr_id = user_id
    	AND fol_parent IS NULL;
	END IF;

    INSERT INTO file (
        fil_fol_id,
        fil_name,
        fil_size,
        fil_type,
        fil_extension,
        fil_path,
        fil_upload_ip,
        fil_hash,
        fil_delete_hash
    )
    VALUES (
        folder_root_id,
        file_name,
        file_size,
        file_type,
        file_extension,
        file_path,
        INET6_ATON(upload_ip),
        file_hash,
        delete_hash
    );

	SELECT LAST_INSERT_ID ( );
END //
DELIMITER ;
