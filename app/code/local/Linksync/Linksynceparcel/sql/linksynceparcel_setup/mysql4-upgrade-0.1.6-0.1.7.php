<?php
$installer = $this;
$installer->startSetup();

$installer->run("
	DROP TABLE IF EXISTS {$this->getTable('linksync_linksynceparcel_manifest')};
	CREATE TABLE IF NOT EXISTS {$this->getTable('linksync_linksynceparcel_manifest')} (
		`manifest_id` int(11) NOT NULL auto_increment primary key,																		   
		`manifest_number` varchar(255) NOT NULL,
		`despatch_date` varchar(40) NOT NULL,
		`label` varchar(255) NOT NULL,
		`number_of_articles` int(11) NOT NULL,
		`number_of_consignments` int(11) NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup(); 
?>