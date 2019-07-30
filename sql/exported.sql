-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.22-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.4.0.5125
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table ofileshare.download_token
CREATE TABLE IF NOT EXISTS `download_token` (
  `dto_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `dto_fil_id` bigint(20) unsigned NOT NULL,
  `dto_usr_id` bigint(20) unsigned DEFAULT NULL,
  `dto_token` char(64) NOT NULL,
  `dto_user_ip` varbinary(16) NOT NULL,
  `dto_create_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dto_expire_date` datetime NOT NULL,
  PRIMARY KEY (`dto_id`),
  KEY `fk_dto_fil_id` (`dto_fil_id`),
  CONSTRAINT `fk_dto_fil_id` FOREIGN KEY (`dto_fil_id`) REFERENCES `file` (`fil_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table ofileshare.download_token: ~0 rows (approximately)
/*!40000 ALTER TABLE `download_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `download_token` ENABLE KEYS */;

-- Dumping structure for table ofileshare.download_tracker
CREATE TABLE IF NOT EXISTS `download_tracker` (
  `dtr_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `dtr_fil_id` bigint(20) unsigned NOT NULL,
  `dtr_usr_id` bigint(20) unsigned DEFAULT NULL,
  `dtr_ip` varbinary(16) NOT NULL,
  `dtr_started_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dtr_updated_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dtr_finished_date` datetime DEFAULT NULL,
  `dtr_status` enum('downloading','finished','error','cancelled') NOT NULL,
  `dtr_start_offset` bigint(20) NOT NULL,
  `dtr_seek_end` bigint(20) NOT NULL,
  PRIMARY KEY (`dtr_id`),
  KEY `dtr_usr_id` (`dtr_usr_id`),
  KEY `dtr_ip` (`dtr_ip`),
  KEY `dtr_started_date` (`dtr_started_date`),
  KEY `dtr_updated_date` (`dtr_updated_date`),
  KEY `dtr_finished_date` (`dtr_finished_date`),
  KEY `dtr_status` (`dtr_status`),
  KEY `fk_dtr_fil_id` (`dtr_fil_id`),
  CONSTRAINT `fk_dtr_fil_id` FOREIGN KEY (`dtr_fil_id`) REFERENCES `file` (`fil_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table ofileshare.download_tracker: ~0 rows (approximately)
/*!40000 ALTER TABLE `download_tracker` DISABLE KEYS */;
/*!40000 ALTER TABLE `download_tracker` ENABLE KEYS */;

-- Dumping structure for table ofileshare.file
CREATE TABLE IF NOT EXISTS `file` (
  `fil_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fil_fpe_id` bigint(20) unsigned NOT NULL DEFAULT '1',
  `fil_fol_id` bigint(20) unsigned NOT NULL,
  `fil_shorturl` char(16) NOT NULL DEFAULT 'TEMP',
  `fil_name` varchar(260) NOT NULL,
  `fil_size` bigint(20) unsigned NOT NULL,
  `fil_type` varchar(140) NOT NULL,
  `fil_extension` varchar(10) NOT NULL,
  `fil_path` varchar(260) NOT NULL,
  `fil_upload_ip` varbinary(16) NOT NULL,
  `fil_upload_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fil_trash` datetime DEFAULT NULL,
  `fil_hash` char(32) DEFAULT NULL,
  `fil_delete_hash` char(32) NOT NULL,
  `fil_unique_hash` char(64) DEFAULT NULL,
  PRIMARY KEY (`fil_id`),
  UNIQUE KEY `fil_delete_hash` (`fil_delete_hash`),
  UNIQUE KEY `fil_unique_hash` (`fil_unique_hash`),
  KEY `fil_fol_id` (`fil_fol_id`),
  KEY `fil_trash` (`fil_trash`),
  KEY `fk_fil_fpe_id` (`fil_fpe_id`),
  CONSTRAINT `fk_fil_fol_id` FOREIGN KEY (`fil_fol_id`) REFERENCES `folder` (`fol_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_fil_fpe_id` FOREIGN KEY (`fil_fpe_id`) REFERENCES `file_permission` (`fpe_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table ofileshare.file: ~0 rows (approximately)
/*!40000 ALTER TABLE `file` DISABLE KEYS */;
/*!40000 ALTER TABLE `file` ENABLE KEYS */;

-- Dumping structure for table ofileshare.file_permission
CREATE TABLE IF NOT EXISTS `file_permission` (
  `fpe_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fpe_name` varchar(64) NOT NULL,
  `fpe_description` text,
  PRIMARY KEY (`fpe_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- Dumping data for table ofileshare.file_permission: ~2 rows (approximately)
/*!40000 ALTER TABLE `file_permission` DISABLE KEYS */;
INSERT INTO `file_permission` (`fpe_id`, `fpe_name`, `fpe_description`) VALUES
	(1, 'Private', 'Can only be accessed by the owner'),
	(2, 'Public', 'Ccan be accessed by everyone with the link');
/*!40000 ALTER TABLE `file_permission` ENABLE KEYS */;

-- Dumping structure for table ofileshare.file_tag
CREATE TABLE IF NOT EXISTS `file_tag` (
  `fta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fta_fil_id` bigint(20) unsigned NOT NULL,
  `fta_tag_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`fta_id`),
  UNIQUE KEY `fta_fil_id_fta_tag_id` (`fta_fil_id`,`fta_tag_id`),
  KEY `fk_fta_tag_id` (`fta_tag_id`),
  CONSTRAINT `fk_fta_fil_id` FOREIGN KEY (`fta_fil_id`) REFERENCES `file` (`fil_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_fta_tag_id` FOREIGN KEY (`fta_tag_id`) REFERENCES `tag` (`tag_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table ofileshare.file_tag: ~0 rows (approximately)
/*!40000 ALTER TABLE `file_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `file_tag` ENABLE KEYS */;

-- Dumping structure for table ofileshare.folder
CREATE TABLE IF NOT EXISTS `folder` (
  `fol_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `fol_usr_id` bigint(20) unsigned NOT NULL,
  `fol_parent` bigint(20) unsigned DEFAULT NULL,
  `fol_name` varchar(260) NOT NULL,
  PRIMARY KEY (`fol_id`),
  KEY `fol_parent` (`fol_parent`),
  KEY `fol_name` (`fol_name`(255)),
  KEY `fk_fol_usr_id` (`fol_usr_id`),
  CONSTRAINT `fk_fol_usr_id` FOREIGN KEY (`fol_usr_id`) REFERENCES `user` (`usr_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table ofileshare.folder: ~0 rows (approximately)
/*!40000 ALTER TABLE `folder` DISABLE KEYS */;
/*!40000 ALTER TABLE `folder` ENABLE KEYS */;

-- Dumping structure for table ofileshare.session
CREATE TABLE IF NOT EXISTS `session` (
  `ses_id` varchar(255) NOT NULL,
  `ses_data` text NOT NULL,
  `ses_updated_on` int(11) NOT NULL,
  PRIMARY KEY (`ses_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table ofileshare.session: ~0 rows (approximately)
/*!40000 ALTER TABLE `session` DISABLE KEYS */;
/*!40000 ALTER TABLE `session` ENABLE KEYS */;

-- Dumping structure for table ofileshare.tag
CREATE TABLE IF NOT EXISTS `tag` (
  `tag_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tag_name` varchar(64) NOT NULL,
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `tag_name` (`tag_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table ofileshare.tag: ~0 rows (approximately)
/*!40000 ALTER TABLE `tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `tag` ENABLE KEYS */;

-- Dumping structure for table ofileshare.user
CREATE TABLE IF NOT EXISTS `user` (
  `usr_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `usr_ule_level` int(11) unsigned NOT NULL DEFAULT '1',
  `usr_username` varchar(64) NOT NULL,
  `usr_title` varchar(12) NOT NULL,
  `usr_firstname` varchar(255) NOT NULL,
  `usr_lastname` varchar(255) NOT NULL,
  `usr_password` char(60) NOT NULL,
  `usr_email` varchar(255) NOT NULL,
  `usr_identifier` char(32) NOT NULL,
  `usr_creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usr_creation_ip` varbinary(16) NOT NULL,
  `usr_password_change_date` datetime DEFAULT NULL,
  `usr_password_change_ip` varbinary(16) DEFAULT NULL,
  `usr_password_reset_hash` char(32) DEFAULT NULL,
  PRIMARY KEY (`usr_id`),
  UNIQUE KEY `usr_username` (`usr_username`),
  UNIQUE KEY `usr_email` (`usr_email`),
  KEY `usr_password_reset_hash` (`usr_password_reset_hash`),
  KEY `fk_usr_ule_level` (`usr_ule_level`),
  CONSTRAINT `fk_usr_ule_level` FOREIGN KEY (`usr_ule_level`) REFERENCES `user_level` (`ule_level`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table ofileshare.user: ~0 rows (approximately)
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
/*!40000 ALTER TABLE `user` ENABLE KEYS */;

-- Dumping structure for table ofileshare.user_level
CREATE TABLE IF NOT EXISTS `user_level` (
  `ule_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ule_level` int(11) unsigned NOT NULL DEFAULT '0',
  `ule_name` varchar(64) NOT NULL,
  `ule_description` text,
  `ule_max_storage` bigint(20) unsigned NOT NULL DEFAULT '0',
  `ule_max_upload_size` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ule_id`),
  UNIQUE KEY `ule_level` (`ule_level`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- Dumping data for table ofileshare.user_level: ~2 rows (approximately)
/*!40000 ALTER TABLE `user_level` DISABLE KEYS */;
INSERT INTO `user_level` (`ule_id`, `ule_level`, `ule_name`, `ule_description`, `ule_max_storage`, `ule_max_upload_size`) VALUES
	(1, 1, 'User', 'Basic user', 5368709120, 2147483648),
	(2, 20, 'Admin', 'Administrator user', 0, 5368709120);
/*!40000 ALTER TABLE `user_level` ENABLE KEYS */;

-- Dumping structure for table ofileshare.user_logon
CREATE TABLE IF NOT EXISTS `user_logon` (
  `ulo_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ulo_usr_id` bigint(20) unsigned NOT NULL,
  `ulo_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ulo_ip` varbinary(16) NOT NULL,
  `ulo_error` smallint(6) NOT NULL DEFAULT '-1',
  `ulo_error_message` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ulo_id`),
  KEY `fk_ulo_usr_id` (`ulo_usr_id`),
  CONSTRAINT `fk_ulo_usr_id` FOREIGN KEY (`ulo_usr_id`) REFERENCES `user` (`usr_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table ofileshare.user_logon: ~0 rows (approximately)
/*!40000 ALTER TABLE `user_logon` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_logon` ENABLE KEYS */;

-- Dumping structure for procedure ofileshare.sp_db_session_destroy
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_db_session_destroy`(
	IN id VARCHAR ( 255 )
)
BEGIN
    DELETE FROM session
    WHERE ses_id = id;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_db_session_gc
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_db_session_gc`(
	IN updated_on INT ( 11 )
)
BEGIN
    DELETE FROM session
    WHERE ses_updated_on < updated_on;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_db_session_read
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_db_session_read`(
	IN id VARCHAR ( 255 )
)
BEGIN
	DECLARE data TEXT;
	
	SELECT
		ses_data INTO data
	FROM session
	WHERE ses_id = id;

	IF ( data IS NOT NULL ) THEN
		SELECT data;
	END IF;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_db_session_write
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_db_session_write`(
	IN id VARCHAR ( 255 ),
    IN data TEXT,
    IN updated_on INT
)
BEGIN
    INSERT INTO session (
        ses_id,
        ses_data,
        ses_updated_on
    )
    VALUES (
        id,
        data,
        updated_on
    )
    ON DUPLICATE KEY UPDATE
        ses_data = data,
        ses_updated_on = updated_on;
	
	SELECT LAST_INSERT_ID ( );
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_download_token_add
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_download_token_add`(
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
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_download_token_load
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_download_token_load`(
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
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_download_tracker_add
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_download_tracker_add`(
    IN file_id BIGINT ( 20 ) UNSIGNED,
    IN user_id BIGINT ( 20 ) UNSIGNED,
    IN ip VARCHAR ( 39 ),
    IN status ENUM ( "downloading", "finished", "error", "cancelled" ),
    IN start_offset BIGINT ( 20 ),
    IN seek_end BIGINT ( 20 )
)
BEGIN
    INSERT INTO download_tracker (
        dtr_fil_id,
        dtr_usr_id,
        dtr_ip,
        dtr_status,
        dtr_start_offset,
        dtr_seek_end
    )
    VALUES (
        file_id,
        user_id,
        INET6_ATON(ip),
        status,
        start_offset,
        seek_end
    );
	
	SELECT LAST_INSERT_ID ( );
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_file_add
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_file_add`(
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
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_file_add_tag
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_file_add_tag`(
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
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_file_all_status
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_file_all_status`(
    IN user_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
    SELECT
        COUNT(*) AS total,
        SUM(fil_size) AS total_size,
        IF(fil_trash IS NULL,"active","trashed") AS status
    FROM file
    INNER JOIN folder ON fol_id = fil_fol_id
    WHERE fol_usr_id = user_id
    GROUP BY IF(fil_trash IS NULL,"active","trashed");
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_file_delete
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_file_delete`(
	IN file_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
    DELETE FROM file_tag
    WHERE fta_fil_id = file_id;

    DELETE FROM file
    WHERE fil_id = file_id;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_file_duplicate_name
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_file_duplicate_name`(
    IN user_id BIGINT ( 20 ) UNSIGNED,
    IN folder_id BIGINT ( 20 ) UNSIGNED,
    IN file_name VARCHAR ( 260 ),
	IN file_id BIGINT ( 20 ) UNSIGNED
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

	IF file_id IS NULL THEN
		SELECT
			COUNT(*)
		FROM file
		INNER JOIN folder ON fol_id = fil_fol_id
		WHERE fil_name = file_name
		AND fil_trash IS NULL
		AND fol_usr_id = user_id
		AND fol_id = folder_root_id;
	ELSE
		SELECT
			COUNT(*)
		FROM file
		INNER JOIN folder ON fol_id = fil_fol_id
		WHERE fil_name = file_name
		AND fil_trash IS NULL
		AND fol_usr_id = user_id
		AND fol_id = folder_root_id
		AND fil_id = file_id;
	END IF;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_file_folder_delete
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_file_folder_delete`(
    IN folder_id BIGINT ( 20 ) UNSIGNED,
    IN user_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
    DECLARE folder_root BIGINT ( 20 ) UNSIGNED;

	SELECT
		fol_id INTO folder_root
    FROM folder
    WHERE fol_usr_id = user_id
    AND fol_parent IS NULL;

    UPDATE file
    SET
        fil_fol_id = folder_root
    WHERE fil_fol_id = folder_id;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_file_load_by_hash
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_file_load_by_hash`(
    IN file_hash CHAR ( 32 )
)
BEGIN
    SELECT
        fol_usr_id AS usr_id,
        fil_id,
        fil_fpe_id,
        fil_fol_id,
        fil_shorturl,
        fil_name,
        fil_size,
        fil_type,
        fil_extension,
        fil_path,
        fil_upload_ip,
        fil_upload_date,
        fil_trash,
        fil_hash,
        fil_delete_hash,
        fil_unique_hash
    FROM file
    INNER JOIN folder ON fol_id = fil_fol_id
    WHERE fil_hash = file_hash;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_file_load_by_id
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_file_load_by_id`(
    IN file_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
    SELECT
        fol_usr_id AS usr_id,
        fil_id,
        fil_fpe_id,
        fil_fol_id,
        fil_shorturl,
        fil_name,
        fil_size,
        fil_type,
        fil_extension,
        fil_path,
        fil_upload_ip,
        fil_upload_date,
        fil_trash,
        fil_hash,
        fil_delete_hash,
        fil_unique_hash
    FROM file
    INNER JOIN folder ON fol_id = fil_fol_id
    WHERE fil_id = file_id;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_file_load_by_shorturl
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_file_load_by_shorturl`(
    IN file_shorturl CHAR ( 16 )
)
BEGIN
    SELECT
        fol_usr_id AS usr_id,
        fil_id,
        fil_fpe_id,
        fil_fol_id,
        fil_shorturl,
        fil_name,
        fil_size,
        fil_type,
        fil_extension,
        fil_path,
        fil_upload_ip,
        fil_upload_date,
        fil_trash,
        fil_hash,
        fil_delete_hash,
        fil_unique_hash
    FROM file
    INNER JOIN folder ON fol_id = fil_fol_id
    WHERE fil_shorturl = file_shorturl;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_file_load_by_unique_hash
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_file_load_by_unique_hash`(
    IN unique_hash CHAR ( 64 )
)
BEGIN
    SELECT
        fol_usr_id AS usr_id,
        fil_id,
        fil_fpe_id,
        fil_fol_id,
        fil_shorturl,
        fil_name,
        fil_size,
        fil_type,
        fil_extension,
        fil_path,
        fil_upload_ip,
        fil_upload_date,
        fil_trash,
        fil_hash,
        fil_delete_hash,
        fil_unique_hash
    FROM file
    INNER JOIN folder ON fol_id = fil_fol_id
    WHERE fil_unique_hash = unique_hash;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_file_trash
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_file_trash`(
    IN file_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
    UPDATE file
    SET
        fil_trash = NOW(),
        fil_hash = NULL
    WHERE fil_id = file_id;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_file_update
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_file_update`(
    IN file_id BIGINT ( 20 ) UNSIGNED,
    IN file_name VARCHAR ( 260 ),
    IN folder_id BIGINT ( 20 ) UNSIGNED,
    IN file_privacy BIGINT ( 20 ) UNSIGNED,
    IN user_id BIGINT ( 20 ) UNSIGNED
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

    UPDATE file
    SET
        fil_name = file_name,
        fil_fol_id = folder_root_id,
        fil_fpe_id = file_privacy
    WHERE fil_id = file_id;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_file_update_shorturl
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_file_update_shorturl`(
    IN file_id BIGINT ( 20 ) UNSIGNED,
    IN file_shorturl CHAR ( 16 )
)
BEGIN
    UPDATE file
    SET
        fil_shorturl = file_shorturl
    WHERE fil_id = file_id;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_file_update_unique_hash
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_file_update_unique_hash`(
    IN file_id BIGINT ( 20 ) UNSIGNED,
    IN file_unique_hash CHAR ( 64)
)
BEGIN
    UPDATE file
    SET
        fil_unique_hash = file_unique_hash
    WHERE fil_id = file_id;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_folder_add
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_folder_add`(
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
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_folder_delete
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_folder_delete`(
	IN folder_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
    DELETE FROM folder
    WHERE fol_id = folder_id;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_folder_duplicate_name
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_folder_duplicate_name`(
    IN user_id BIGINT ( 20 ) UNSIGNED,
    IN folder_parent BIGINT ( 20 ) UNSIGNED,
    IN folder_name VARCHAR ( 260 ),
	IN folder_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
	DECLARE folder_root_id BIGINT ( 20 ) UNSIGNED DEFAULT folder_parent;
	
	IF folder_parent IS NULL THEN
		SELECT
		 	fol_id INTO folder_root_id
    	FROM folder
    	WHERE fol_usr_id = user_id
    	AND fol_parent IS NULL;
	END IF;

	IF folder_id IS NULL THEN
		SELECT
			fol_id
		FROM folder
		WHERE fol_name = folder_name
		AND fol_parent = folder_root_id
		AND fol_usr_id = user_id;
	ELSE
		SELECT
			fol_id
		FROM folder
		WHERE fol_name = folder_name
		AND fol_parent = folder_root_id
		AND fol_usr_id = user_id
		AND fol_id != folder_id;
	END IF;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_folder_load_by_id
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_folder_load_by_id`(
    IN folder_id BIGINT ( 20 ) ,
    IN return_root BOOLEAN
)
BEGIN
    IF return_root = TRUE THEN
        SELECT
            fol_id,
            fol_usr_id,
            fol_parent,
            fol_name
        FROM folder
        WHERE fol_id = folder_id
        GROUP BY fol_name ASC;
    ELSE
        SELECT
            fol_id,
            fol_usr_id,
            fol_parent,
            fol_name
        FROM folder
        WHERE fol_id = folder_id
        AND fol_parent IS NOT NULL
        GROUP BY fol_name ASC;
    END IF;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_folder_load_by_user
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_folder_load_by_user`(
    IN user_id BIGINT ( 20 ) UNSIGNED,
    IN return_root BOOLEAN
)
BEGIN
	IF return_root = TRUE THEN
        SELECT
            fol_id,
            fol_usr_id,
            fol_parent,
            fol_name
        FROM folder
        WHERE fol_usr_id = user_id
        GROUP BY fol_name ASC;
    ELSE
        SELECT
            fol_id,
            fol_usr_id,
            fol_parent,
            fol_name
        FROM folder
        WHERE fol_usr_id = user_id
        AND fol_parent IS NOT NULL
        GROUP BY fol_name ASC;
    END IF;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_folder_update
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_folder_update`(
    IN user_id BIGINT ( 20 ) UNSIGNED,
    IN folder_id BIGINT ( 20 ) UNSIGNED,
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

    UPDATE folder
    SET
        fol_parent = parent_root,
        fol_name = name
    WHERE fol_id = folder_id;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_user_level_name
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_user_level_name`(
	IN level_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
	DECLARE data VARCHAR ( 64 );
	
	SELECT
		ule_name INTO data
	FROM user_level
	WHERE ule_level = level_id;

	IF ( data IS NOT NULL ) THEN
		SELECT data;
	END IF;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_user_load_by_email
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_user_load_by_email`(
    IN email VARCHAR ( 255 )
)
BEGIN
	SELECT
		usr_id,
        usr_ule_level,
        usr_title,
        usr_firstname,
        usr_lastname,
        usr_username,
        usr_password,
        usr_email,
        usr_identifier,
        usr_creation_date,
        INET6_NTOA(usr_creation_ip),
        usr_password_change_date,
        INET6_NTOA(usr_password_change_ip),
        usr_password_reset_hash
	FROM user
	WHERE usr_email = email;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_user_load_by_id
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_user_load_by_id`(
    IN user_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
	SELECT
		usr_id,
        usr_ule_level,
        usr_title,
        usr_firstname,
        usr_lastname,
        usr_username,
        usr_password,
        usr_email,
        usr_identifier,
        usr_creation_date,
        INET6_NTOA(usr_creation_ip),
        usr_password_change_date,
        INET6_NTOA(usr_password_change_ip),
        usr_password_reset_hash
	FROM user
	WHERE usr_id = user_id;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_user_load_by_password_reset_hash
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_user_load_by_password_reset_hash`(
    IN password_reset_hash CHAR ( 32 )
)
BEGIN
	SELECT
		usr_id,
        usr_ule_level,
        usr_title,
        usr_firstname,
        usr_lastname,
        usr_username,
        usr_password,
        usr_email,
        usr_identifier,
        usr_creation_date,
        INET6_NTOA(usr_creation_ip),
        usr_password_change_date,
        INET6_NTOA(usr_password_change_ip),
        usr_password_reset_hash
	FROM user
	WHERE usr_password_reset_hash = password_reset_hash;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_user_load_by_username
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_user_load_by_username`(
    IN username VARCHAR ( 64 )
)
BEGIN
	SELECT
		usr_id,
        usr_ule_level,
        usr_title,
        usr_firstname,
        usr_lastname,
        usr_username,
        usr_password,
        usr_email,
        usr_identifier,
        usr_creation_date,
        INET6_NTOA(usr_creation_ip),
        usr_password_change_date,
        INET6_NTOA(usr_password_change_ip),
        usr_password_reset_hash
	FROM user
	WHERE usr_username = username;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_user_logon_add
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_user_logon_add`(
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
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_user_owns_folder
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_user_owns_folder`(
    IN user_id BIGINT ( 20 ) UNSIGNED,
    IN folder_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
    SELECT
        fol_id,
        fol_usr_id
    FROM folder
    WHERE fol_id = folder_id
    AND fol_usr_id = user_id
    LIMIT 1;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_user_register
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_user_register`(
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
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_user_total_file_size
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_user_total_file_size`(
    IN user_id BIGINT ( 20 ) UNSIGNED
)
BEGIN
	SELECT
		SUM(fil_size)
	FROM file
	INNER JOIN folder ON fol_id = fil_fol_id
	WHERE fol_usr_id = user_id
    AND fil_trash IS NULL;
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_user_update
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_user_update`(
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
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_user_update_password
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_user_update_password`(
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
END//
DELIMITER ;

-- Dumping structure for procedure ofileshare.sp_user_update_password_reset_hash
DELIMITER //
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_user_update_password_reset_hash`(
	IN user_id BIGINT ( 20 ) UNSIGNED,
    IN password_reset_hash CHAR ( 32 )
)
BEGIN
    UPDATE user
    SET
        usr_password_reset_hash = password_reset_hash
    WHERE usr_id = user_id;
END//
DELIMITER ;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
