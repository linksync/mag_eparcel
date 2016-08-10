<?php
$installer = $this;
$installer->startSetup();

$installer->run("
	DROP TABLE IF EXISTS {$this->getTable('linksync_linksynceparcel_article')};
	CREATE TABLE IF NOT EXISTS {$this->getTable('linksync_linksynceparcel_article')} (
		`order_id` int(11) NOT NULL default '0',
		`consignment_number` varchar(255) NOT NULL,
		`article_number` varchar(255) NOT NULL,
		`actual_weight` varchar(255) NOT NULL,
		`article_description` varchar(255) NOT NULL,
		`cubic_weight` varchar(255) NOT NULL,
		`height` varchar(255) NOT NULL,
		`is_transit_cover_required` varchar(255) NOT NULL,
		`transit_cover_amount` varchar(255) NOT NULL,
		`length` varchar(40) NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup(); 
?>