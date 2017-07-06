<?php

$installer = $this;
$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS `{$installer->getTable('translator_cache')}`;
CREATE TABLE `{$installer->getTable('translator_cache')}` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NULL,
  `register` longblob NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->run("
		CREATE TABLE IF NOT EXISTS `{$installer->getTable('translator/string')}` (
		`string_id` int(11) NOT NULL auto_increment,
		`string` text,
		`module` text,
		`parameters` blob NULL,
		`status` tinyint(1) NOT NULL default 1,
		PRIMARY KEY  (`string_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	");

$installer->run("
		CREATE TABLE IF NOT EXISTS `{$installer->getTable('translator/translation')}` (
		`translation_id` int(11) NOT NULL auto_increment,
		`translation` text,
		`locale` text,
		`store_id` smallint(5) unsigned NOT NULL default 0,
		`frontend` tinyint(1) NOT NULL default 1,
		`adminhtml` tinyint(1) NOT NULL default 1,
		`install` tinyint(1) NOT NULL default 1,
		`primary` tinyint(1) NOT NULL default 1,
		`string_id` int(11) NOT NULL,
		PRIMARY KEY (`translation_id`),
		INDEX (`string_id`),
		FOREIGN KEY (`string_id`) REFERENCES {$installer->getTable('translator/string')}(`string_id`)
                      ON DELETE CASCADE
                      ON UPDATE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	");

$installer->run("
		CREATE TABLE IF NOT EXISTS `{$installer->getTable('translator/path')}` (
		`path_id` int(11) NOT NULL auto_increment,
		`path` text,
		`file` text,
		`offset` int(11),
		`string_id` int(11) NOT NULL,
		PRIMARY KEY (`path_id`),
		INDEX (`string_id`),
		FOREIGN KEY (`string_id`) REFERENCES {$installer->getTable('translator/string')}(`string_id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

$installer->endSetup();