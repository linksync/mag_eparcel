<?php
$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE  `".$this->getTable('linksync_linksynceparcel_consignment')."` ADD INDEX `con_order_id_despatched` ( `order_id` , `despatched` )");
$installer->run("ALTER TABLE  `".$this->getTable('linksync_linksynceparcel_consignment')."` ADD INDEX `con_consignment_number` ( `consignment_number`)");
$installer->run("ALTER TABLE  `".$this->getTable('linksync_linksynceparcel_consignment')."` ADD INDEX `con_manifest_number` ( `manifest_number`)");
$installer->run("ALTER TABLE  `".$this->getTable('linksync_linksynceparcel_article')."`  ADD INDEX `art_consignment_number` ( `consignment_number`)");
$installer->run("ALTER TABLE  `".$this->getTable('linksync_linksynceparcel_manifest')."` ADD INDEX `man_manifest_number` ( `manifest_number`)");

$installer->endSetup(); 
?>