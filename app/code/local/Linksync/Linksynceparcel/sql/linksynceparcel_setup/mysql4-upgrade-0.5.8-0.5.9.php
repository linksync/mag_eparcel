<?php
$installer = $this;
$installer->startSetup();

$installer->run("
				
	DROP TABLE IF EXISTS {$this->getTable('linksync_linksynceparcel_nonlinksync')};
	CREATE TABLE IF NOT EXISTS {$this->getTable('linksync_linksynceparcel_nonlinksync')} (
		`id` int(11) NOT NULL auto_increment,
		`method` varchar(255) NOT NULL,
		`charge_code` varchar(255) NOT NULL,
		PRIMARY KEY  (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup(); 
?>