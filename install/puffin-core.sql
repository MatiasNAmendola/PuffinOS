-- Uncomment Line 3 if using this script to *re*install.

DROP USER 'puffin'@'localhost';
DROP DATABASE IF EXISTS `puffin_user`;
DROP DATABASE IF EXISTS `puffin_kernel`;

CREATE DATABASE `puffin_user`;
CREATE DATABASE `puffin_kernel`;

CREATE USER 'puffin'@'localhost' IDENTIFIED BY 'P@ssw0rd';
GRANT USAGE ON `puffin_user`.* TO 'puffin'@'localhost';
GRANT USAGE ON `puffin_kernel`.* TO 'puffin'@'localhost';
GRANT ALL PRIVILEGES ON `puffin_user`.* TO 'puffin'@'localhost';
GRANT ALL PRIVILEGES ON `puffin_kernel`.* TO 'puffin'@'localhost';

USE `puffin_kernel`;

CREATE TABLE `user` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`username` VARCHAR(50) NOT NULL UNIQUE,
	`password` VARCHAR(40) NOT NULL,
	`superuser` TINYINT(1) NOT NULL DEFAULT 0,
	`locked` TINYINT(1) NOT NULL DEFAULT 0,
	`comment` TEXT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE `group` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`groupname` VARCHAR(50) NOT NULL UNIQUE,
	PRIMARY KEY (`id`)
);

CREATE TABLE `group_member` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`user` INT UNSIGNED NOT NULL,
	`group` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`user`)
		REFERENCES `user` (`id`),
	FOREIGN KEY (`group`)
		REFERENCES `group` (`id`),
	UNIQUE KEY (`user`, `group`)
);

CREATE TABLE `startup_application` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`application` VARCHAR(200) NOT NULL,
	`enabled` TINYINT(1) NOT NULL DEFAULT 1,
	`sequence` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`)
);

INSERT INTO `user` (
	`username`, `password`, `superuser`, `comment`
) VALUES (
	'root', SHA1('puffin'), 1, 'Default Administration Account'
);

INSERT INTO `group` (
	`groupname`
) VALUES (
	'admin'
);

INSERT INTO `group_member` (
	`user`, `group`
) VALUES (
	1, 1
);

INSERT INTO `startup_application` (
	`application`, `sequence`
) VALUES (
	"PLM", 1
);

DELIMITER $$

CREATE PROCEDURE `get_startup_applications` ()
BEGIN
	SELECT `application` FROM `startup_application` WHERE `enabled` = 1 ORDER BY `sequence`;
END;$$

CREATE PROCEDURE `get_group_ids` (UserID INT UNSIGNED)
BEGIN
	SELECT 
		g.id
	FROM
		`group_member` gm INNER JOIN
		`group` g ON gm.group = g.id
	WHERE
		gm.user = UserID;
END;$$

DELIMITER ;

-- Userspace operations

USE `puffin_user`;

CREATE TABLE `file` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`filename` VARCHAR(250) NOT NULL,
	`full_file_path` VARCHAR(250) NOT NULL,
	`parent` BIGINT UNSIGNED NULL,
	`is_directory` TINYINT(1) NOT NULL DEFAULT 0,
	`owner` INT UNSIGNED NOT NULL,
	`world_readable` TINYINT(1) NOT NULL DEFAULT 1,
	`world_writeable` TINYINT(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`owner`)
		REFERENCES `user` (`id`)
);

CREATE TABLE `file_read_user` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`user` INT UNSIGNED NOT NULL,
	`file` BIGINT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`user`)
		REFERENCES `user` (`id`),
	FOREIGN KEY (`file`)
		REFERENCES `file` (`id`)
);

CREATE TABLE `file_write_user` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`user` INT UNSIGNED NOT NULL,
	`file` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`user`)
		REFERENCES `user` (`id`),
	FOREIGN KEY (`file`)
		REFERENCES `file` (`id`)
);

CREATE TABLE `file_read_group` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`group` INT UNSIGNED NOT NULL,
	`file` BIGINT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`group`)
		REFERENCES `group` (`id`),
	FOREIGN KEY (`file`)
		REFERENCES `file` (`id`)
);

CREATE TABLE `file_write_group` (
	`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`group` INT UNSIGNED NOT NULL,
	`file` BIGINT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`group`)
		REFERENCES `group` (`id`),
	FOREIGN KEY (`file`)
		REFERENCES `file` (`id`)
);
