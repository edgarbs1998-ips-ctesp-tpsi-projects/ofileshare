ALTER TABLE user_logon
	ADD CONSTRAINT fk_ulo_usr_id
		FOREIGN KEY (ulo_usr_id) REFERENCES user (usr_id)
		ON UPDATE RESTRICT ON DELETE RESTRICT;
