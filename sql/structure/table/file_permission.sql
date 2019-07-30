CREATE TABLE file_permission (
	fpe_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	fpe_name VARCHAR(64) NOT NULL,
	fpe_description TEXT NULL DEFAULT NULL,
	PRIMARY KEY (fpe_id)
);
