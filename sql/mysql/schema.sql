DROP TABLE IF EXISTS `sso_jwt_token`;
CREATE TABLE `sso_jwt_token` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `token_id` CHAR(32) NOT NULL,
  `usage_date` int(11) UNSIGNED NOT NULL,
  `token_jwt` TEXT DEFAULT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `sso_jwt_logs`;
CREATE TABLE `sso_jwt_logs` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `date` int(11) unsigned NOT NULL,
  `service_provider` varchar(255) default NULL,
  `user_id` int(11) unsigned default NULL,
  `message` text default NULL,
  `error` text default NULL,
  `backtrace` text default NULL,
  `ip` char(16) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
