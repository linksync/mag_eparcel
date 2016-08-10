<?php
$installer = $this;
$installer->startSetup();

$installer->run("
	DROP TABLE IF EXISTS {$this->getTable('linksync_linksynceparcel_tabelrate')};
	CREATE TABLE IF NOT EXISTS {$this->getTable('linksync_linksynceparcel_tabelrate')} (
		`pk` int(10) unsigned NOT NULL auto_increment,
		`website_id` int(11) NOT NULL default '0',
		`dest_country_id` varchar(4) NOT NULL default '0',
		`dest_region_id` int(10) NOT NULL default '0',
		`dest_zip` varchar(10) NOT NULL default '',
		`condition_name` varchar(20) NOT NULL default '',
		`condition_from_value` decimal(12,4) NOT NULL default '0.0000',
		`condition_to_value` decimal(12,4) NOT NULL default '0.0000',
		`price` decimal(12,4) NOT NULL default '0.0000',
		`price_per_kg` decimal(12,4) NOT NULL default '0.0000',
		`cost` decimal(12,4) NOT NULL default '0.0000',
		`delivery_type` varchar(50) NOT NULL default '',
		`charge_code` varchar(50) NULL default NULL,
		PRIMARY KEY  (`pk`),
		UNIQUE KEY `dest_country` ( `website_id` , `dest_country_id` , `dest_region_id` , `dest_zip` , `condition_name` , `condition_to_value` , `delivery_type`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
	DROP TABLE IF EXISTS {$this->getTable('linksync_linksynceparcel_authority')};
	CREATE TABLE IF NOT EXISTS {$this->getTable('linksync_linksynceparcel_authority')} (
		`authority_id` int(10) unsigned NOT NULL auto_increment,
		`order_id` int(11) NOT NULL default '0',
		`instructions` varchar(128) NOT NULL,
		PRIMARY KEY  (`authority_id`),
		UNIQUE KEY (`order_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup(); 
?>