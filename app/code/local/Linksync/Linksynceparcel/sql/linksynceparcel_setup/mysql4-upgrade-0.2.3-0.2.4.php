<?php
$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE  `".$this->getTable('linksync_linksynceparcel_authority')."` CHANGE  `instructions` `instructions` text");

$installer->endSetup(); 
?>