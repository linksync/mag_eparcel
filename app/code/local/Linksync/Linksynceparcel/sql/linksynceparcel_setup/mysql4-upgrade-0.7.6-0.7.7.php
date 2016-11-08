<?php
$installer = $this;
$installer->startSetup();

$installer->run("
	DROP TABLE IF EXISTS {$this->getTable('linksync_linksynceparcel_international_fields')};
	CREATE TABLE IF NOT EXISTS {$this->getTable('linksync_linksynceparcel_international_fields')} (
		`order_id` int(11) NOT NULL DEFAULT '0',
		`consignment_number` varchar(128) NOT NULL,
		`add_date` varchar(40) NOT NULL,
		`modify_date` varchar(40) NOT NULL,
		`insurance` tinyint(1) NOT NULL DEFAULT '0',
		`insurance_value` varchar(255) NOT NULL,
		`export_declaration_number` varchar(255) NOT NULL,
		`declared_value` tinyint(1) NOT NULL DEFAULT '0',
		`declared_value_text` varchar(255) NOT NULL,
		`has_commercial_value` tinyint(1) NOT NULL DEFAULT '0', 
		`product_classification` int(11) NOT NULL DEFAULT '991',
		`product_classification_text` varchar(255) NOT NULL,
		`country_origin` varchar(255) DEFAULT NULL,
		`hs_tariff` varchar(255) DEFAULT NULL,
		`default_contents` varchar(255) DEFAULT NULL,
		`ship_country` varchar(255) DEFAULT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	ALTER TABLE {$this->getTable('linksync_linksynceparcel_consignment')} 
		ADD `delivery_country` varchar(10),
		ADD `customdocs` varchar(255),
		ADD `is_customdocs_printed` tinyint(1) NOT NULL DEFAULT '0' AFTER `is_label_printed`,
		ADD `delivery_instruction` varchar(300),
		ADD `safe_drop` tinyint(1);
	ALTER TABLE {$this->getTable('linksync_linksynceparcel_nonlinksync')} 
		ADD `service_type` varchar(100);
");

$installer->endSetup(); 
?>