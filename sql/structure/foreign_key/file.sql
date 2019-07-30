ALTER TABLE file
	ADD CONSTRAINT fk_fil_fpe_id
		FOREIGN KEY (fil_fpe_id) REFERENCES file_permission (fpe_id)
		ON UPDATE RESTRICT ON DELETE RESTRICT,
	ADD CONSTRAINT fk_fil_fol_id
		FOREIGN KEY (fil_fol_id) REFERENCES folder (fol_id)
		ON UPDATE RESTRICT ON DELETE RESTRICT;