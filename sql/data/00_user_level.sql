INSERT INTO user_level (	ule_level,
							ule_name,
							ule_description,
							ule_max_storage,
							ule_max_upload_size )
VALUES (	1,
			'User',
			'Basic user',
			5368709120,
			2147483648 );

INSERT INTO user_level (	ule_level,
							ule_name,
							ule_description,
							ule_max_storage,
							ule_max_upload_size )
VALUES (	20,
			'Admin',
			'Administrator user',
			0,
			5368709120 );