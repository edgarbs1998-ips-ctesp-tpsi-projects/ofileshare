DELIMITER //
DROP PROCEDURE IF EXISTS sp_folder_add //
CREATE PROCEDURE sp_folder_add (
    IN user_id BIGINT ( 20 ) UNSIGNED,
    IN parent BIGINT ( 20 ) UNSIGNED,
    IN name VARCHAR ( 260 )
)
BEGIN
    DECLARE parent_root BIGINT ( 20 ) UNSIGNED DEFAULT parent;

    IF parent IS NULL THEN
		SELECT
		 	fol_id INTO parent_root
    	FROM folder
    	WHERE fol_usr_id = user_id
    	AND fol_parent IS NULL;
	END IF;

    INSERT INTO folder (
        fol_usr_id,
        fol_parent,
        fol_name
    )
    VALUES (
        user_id,
        parent_root,
        name
    );
	
	SELECT LAST_INSERT_ID ( );
END //
DELIMITER ;
