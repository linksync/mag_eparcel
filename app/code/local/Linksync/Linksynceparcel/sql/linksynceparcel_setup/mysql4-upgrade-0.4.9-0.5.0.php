<?php
$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE  `".$this->getTable('linksync_linksynceparcel_free_shipping')."` ADD  `from_amount` decimal(12,2) DEFAULT '0'");
$installer->run("ALTER TABLE  `".$this->getTable('linksync_linksynceparcel_free_shipping')."` ADD  `to_amount` decimal(12,2) DEFAULT '0'");
$installer->run("ALTER TABLE  `".$this->getTable('linksync_linksynceparcel_free_shipping')."` DROP INDEX charge_code");

$installer->endSetup(); 
?>