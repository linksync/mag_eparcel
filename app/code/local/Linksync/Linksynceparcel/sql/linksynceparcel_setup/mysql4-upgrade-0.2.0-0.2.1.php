<?php
$installer = $this;
$installer->startSetup();

$installer->run("
	DROP TABLE IF EXISTS {$this->getTable('linksync_linksynceparcel_free_shipping')};
	CREATE TABLE IF NOT EXISTS {$this->getTable('linksync_linksynceparcel_free_shipping')} (
		`id` int(10) unsigned NOT NULL auto_increment,
		`charge_code` varchar(128) NOT NULL,
		`minimum_amount` decimal(12,4) NOT NULL,
		`status` tinyint(1) NOT NULL DEFAULT '1',
		PRIMARY KEY  (`id`),
		UNIQUE KEY (`charge_code`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup(); 
?>