CREATE DATABASE `ofileshare` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE USER 'ofileshare'@'localhost' IDENTIFIED BY 'Qp8eVm4GdyS8JEmZ';

GRANT EXECUTE, SELECT, INSERT, UPDATE, DELETE ON `ofileshare`.* TO 'ofileshare'@'localhost';
