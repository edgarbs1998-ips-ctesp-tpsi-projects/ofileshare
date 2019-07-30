ALTER TABLE download_token
	ADD CONSTRAINT fk_dto_fil_id
		FOREIGN KEY (dto_fil_id) REFERENCES file (fil_id)
		ON UPDATE RESTRICT ON DELETE RESTRICT;
