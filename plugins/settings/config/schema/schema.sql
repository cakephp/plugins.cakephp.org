CREATE TABLE `settings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `key` char(64) NOT NULL,
  `value` char(255) NOT NULL,
  `title` char(255) NOT NULL,
  `description` char(255) NOT NULL,
  `input_type` char(255) NOT NULL DEFAULT 'text',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8