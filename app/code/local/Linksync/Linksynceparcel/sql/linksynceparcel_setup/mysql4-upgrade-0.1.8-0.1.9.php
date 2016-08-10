<?php
$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE  `".$this->getTable('linksync_linksynceparcel_consignment')."` ADD  `is_next_manifest` tinyint(1) NOT NULL DEFAULT '0'");
$installer->run("ALTER TABLE  `".$this->getTable('linksync_linksynceparcel_consignment')."` CHANGE  `not_open` `despatched` tinyint(1) NOT NULL DEFAULT '0'");

$installer->endSetup(); 
?>