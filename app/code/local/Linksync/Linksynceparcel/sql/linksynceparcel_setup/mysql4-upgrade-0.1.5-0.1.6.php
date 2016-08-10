<?php
$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE  `".$this->getTable('linksync_linksynceparcel_consignment')."` ADD  `not_open` tinyint(1), ADD  `label` varchar(255)");

$installer->endSetup(); 
?>