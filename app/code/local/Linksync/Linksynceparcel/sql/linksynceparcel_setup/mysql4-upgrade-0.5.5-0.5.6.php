<?php
$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE  `".$this->getTable('linksync_linksynceparcel_consignment')."` ADD  `general_linksynceparcel_shipping_chargecode` varchar(255)");

$installer->run("
	DROP TABLE IF EXISTS {$this->getTable('linksync_linksynceparcel_article_preset')};
	CREATE TABLE IF NOT EXISTS {$this->getTable('linksync_linksynceparcel_article_preset')} (
		`id` int(11) NOT NULL auto_increment,
		`name` varchar(255) NOT NULL,
		`weight` varchar(40) NOT NULL,
		`width` varchar(40) NOT NULL,
		`height` varchar(40) NOT NULL,
		`length` varchar(40) NOT NULL,
		`status` tinyint(1) NOT NULL DEFAULT '1',
		PRIMARY KEY  (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup(); 
?>