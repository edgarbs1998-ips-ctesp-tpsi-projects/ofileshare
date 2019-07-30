CREATE TABLE user_level (
	ule_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	ule_level INT(11) UNSIGNED NOT NULL DEFAULT 0,
	ule_name VARCHAR(64) NOT NULL,
	ule_description TEXT NULL DEFAULT NULL,
	ule_max_storage BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
	ule_max_upload_size BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (ule_id),
	UNIQUE KEY ule_level (ule_level)
);
