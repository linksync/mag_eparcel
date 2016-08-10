<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn(
        $this->getTable('sales/order'), 
        'is_address_valid',
        'TINYINT(1) DEFAULT 0'
        );

$installer->endSetup(); 
?>