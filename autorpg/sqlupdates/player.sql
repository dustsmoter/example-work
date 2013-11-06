CREATE TABLE `user` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `username` varchar(255) NOT NULL DEFAULT '',
 `password` varchar(255) NOT NULL DEFAULT '',
 `email` varchar(64) NOT NULL DEFAULT '',
 `date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 PRIMARY KEY (`id`),
 KEY `user_pass_idx` (`username`,`password`),
 KEY `email_idx` (`email`)
) ENGINE=InnoDB