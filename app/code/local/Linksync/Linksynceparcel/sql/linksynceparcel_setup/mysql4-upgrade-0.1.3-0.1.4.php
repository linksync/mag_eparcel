<?php
$installer = $this;
$installer->startSetup();

$installer->run("
	ALTER TABLE {$this->getTable('linksync_linksynceparcel_consignment')} 
		ADD `delivery_signature_allowed` varchar(255),
		ADD `print_return_labels` varchar(255) NOT NULL,
		ADD `contains_dangerous_goods` varchar(255) NOT NULL,
		ADD `partial_delivery_allowed` varchar(255) NOT NULL,
		ADD `cash_to_collect` varchar(255) NOT NULL
	;
");

$installer->endSetup(); 
?>