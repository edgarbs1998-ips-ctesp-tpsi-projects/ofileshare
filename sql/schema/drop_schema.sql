DROP DATABASE IF EXISTS `ofileshare`;

REVOKE ALL PRIVILEGES ON `ofileshare`.* FROM 'ofileshare'@'localhost';

DROP USER IF EXISTS 'ofileshare'@'localhost';
