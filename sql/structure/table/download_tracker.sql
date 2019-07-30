CREATE TABLE download_tracker (
	dtr_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	dtr_fil_id BIGINT(20) UNSIGNED NOT NULL,
	dtr_usr_id BIGINT(20) UNSIGNED NULL DEFAULT NULL,
	dtr_ip VARBINARY(16) NOT NULL,
	dtr_started_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	dtr_updated_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	dtr_finished_date DATETIME NULL DEFAULT NULL,
	dtr_status ENUM("downloading","finished","error","cancelled") NOT NULL,
	dtr_start_offset BIGINT(20) NOT NULL,
	dtr_seek_end BIGINT(20) NOT NULL,
	PRIMARY KEY (dtr_id),
	INDEX dtr_usr_id (dtr_usr_id),
	INDEX dtr_ip (dtr_ip),
	INDEX dtr_started_date (dtr_started_date),
	INDEX dtr_updated_date (dtr_updated_date),
	INDEX dtr_finished_date (dtr_finished_date),
	INDEX dtr_status (dtr_status)
);
