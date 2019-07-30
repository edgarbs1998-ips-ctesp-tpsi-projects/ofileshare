ALTER TABLE download_tracker
	ADD CONSTRAINT fk_dtr_fil_id
		FOREIGN KEY (dtr_fil_id) REFERENCES file (fil_id)
		ON UPDATE RESTRICT ON DELETE RESTRICT;
