<?php
$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE  `".$this->getTable('linksync_linksynceparcel_consignment')."` ADD  `is_label_printed` tinyint(1) NOT NULL DEFAULT '0', ADD `is_label_created` tinyint(1) NOT NULL DEFAULT '0'");

$installer->endSetup(); 
?>