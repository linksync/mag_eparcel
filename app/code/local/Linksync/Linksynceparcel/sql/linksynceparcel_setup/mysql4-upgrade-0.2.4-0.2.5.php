<?php
$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE  `".$this->getTable('linksync_linksynceparcel_tabelrate')."` CHANGE  `dest_region_id` `dest_region_id` varchar(255)");

$installer->endSetup(); 
?>