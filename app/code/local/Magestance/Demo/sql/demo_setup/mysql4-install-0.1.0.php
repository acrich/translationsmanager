<?php

$installer = $this;

$installer->startSetup();
/*
$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('demo')};
CREATE TABLE {$this->getTable('demo')} (
  `demo_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `filename` varchar(255) NOT NULL default '',
  `content` text NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`demo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");
*/
$installer->addEntityType('demo_translate',Array(
//entity_model is the URL you'd pass into a Mage::getModel() call
		'entity_model'          =>'core/translate',
		//blank for now
		'attribute_model'       =>'',
		//table refers to the resource URI complexworld/eavblogpost
//<complexworld_resource_eav_mysql4>...<eavblogpost><table>eavblog_posts</table>
		'table'         =>'demo/translate',
		//blank for now, but can also be eav/entity_increment_numeric
		'increment_model'       =>'',
		//appears that this needs to be/can be above "1" if we're using eav/entity_increment_numeric
		'increment_per_store'   =>'0'
));

$installer->createEntityTables(
		$this->getTable('demo/translate')
);

$installer->endSetup();