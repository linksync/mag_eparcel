<?php
$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE  `".$this->getTable('linksync_linksynceparcel_article')."` ADD  `width` varchar(255)");

$installer->endSetup(); 
?>