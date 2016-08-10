<?php
$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE  `".$this->getTable('linksync_linksynceparcel_consignment')."` ADD  `email_notification` tinyint(1) DEFAULT '0'");

$installer->endSetup(); 
?>