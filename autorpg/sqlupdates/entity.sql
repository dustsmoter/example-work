CREATE TABLE `entity` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `user_id` int(11) NOT NULL,
 `owner_entity_id` mediumint(5) NOT NULL DEFAULT '0',
 `entity_type` enum('creature','location','item') NOT NULL DEFAULT 'creature',
 `entity_type_id` mediumint(5) NOT NULL,
 `tags` mediumint(5) NOT NULL,
 `health` mediumint(5) NOT NULL,
 `level` tinyint(3) NOT NULL DEFAULT '1',
 `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 KEY `id` (`id`,`user_id`),
 KEY `owner_idx` (`owner_entity_id`)
) ENGINE=InnoDB

CREATE TABLE `creature` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `strength` tinyint(3) NOT NULL,
 `agility` tinyint(3) NOT NULL,
 `magic` tinyint(3) NOT NULL,
 `willpower` tinyint(3) NOT NULL,
 `constitution` tinyint(3) NOT NULL,
 `char_name` varchar(64) NOT NULL,
 `tags` mediumint(5) NOT NULL,
 `name` varchar(64) NOT NULL,
 `description` text NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB

CREATE TABLE `item` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `damage` mediumint(5) NOT NULL,
 `defense` mediumint(5) NOT NULL,
 `name` varchar(64) NOT NULL,
 `description` text NOT NULL,
 `tags` mediumint(5) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB

CREATE TABLE `location` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `name` varchar(64) NOT NULL,
 `description` text NOT NULL,
 `tags` mediumint(5) NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB