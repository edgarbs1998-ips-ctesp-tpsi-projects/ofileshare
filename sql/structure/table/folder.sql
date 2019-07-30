CREATE TABLE folder (
	fol_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	fol_usr_id BIGINT(20) UNSIGNED NOT NULL,
	fol_parent BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	fol_name VARCHAR(260) NOT NULL,
	PRIMARY KEY (fol_id),
	INDEX fol_parent (fol_parent),
	INDEX fol_name (fol_name)
);
