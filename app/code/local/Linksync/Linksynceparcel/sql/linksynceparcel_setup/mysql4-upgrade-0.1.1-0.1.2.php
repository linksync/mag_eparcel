<?php
$installer = $this;
$installer->startSetup();

$installer->run("
	DROP TABLE IF EXISTS {$this->getTable('linksync_linksynceparcel_consignment')};
	CREATE TABLE IF NOT EXISTS {$this->getTable('linksync_linksynceparcel_consignment')} (
		`order_id` int(11) NOT NULL default '0',
		`consignment_number` varchar(128) NOT NULL,
		`add_date` varchar(40) NOT NULL,
		`modify_date` varchar(40) NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup(); 
?>