<?php
$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE  `".$this->getTable('linksync_linksynceparcel_consignment')."` ADD  `manifest_number` varchar(255)");

$installer->endSetup(); 
?>