CREATE TABLE file_tag (
	fta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	fta_fil_id BIGINT(20) UNSIGNED NOT NULL,
	fta_tag_id BIGINT(20) UNSIGNED NOT NULL,
	PRIMARY KEY (fta_id),
	UNIQUE INDEX fta_fil_id_fta_tag_id (fta_fil_id, fta_tag_id)
);
