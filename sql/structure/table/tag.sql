CREATE TABLE tag (
	tag_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	tag_name VARCHAR(64) NOT NULL,
	PRIMARY KEY (tag_id),
	UNIQUE INDEX tag_name (tag_name)
);
