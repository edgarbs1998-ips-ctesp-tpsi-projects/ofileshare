ALTER TABLE user
	ADD CONSTRAINT fk_usr_ule_level
		FOREIGN KEY (usr_ule_level) REFERENCES user_level (ule_level)
		ON UPDATE RESTRICT ON DELETE RESTRICT;
