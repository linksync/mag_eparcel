<?php
class Linksync_Linksynceparcel_Model_Mysql4_Consignment extends Mage_Core_Model_Mysql4_Abstract {
    public function _construct() {
        $this->_init('linksynceparcel/consignment', 'consignment_number');
    }
}