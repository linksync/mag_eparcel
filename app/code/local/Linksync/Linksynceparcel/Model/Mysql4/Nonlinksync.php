<?php
class Linksync_Linksynceparcel_Model_Mysql4_Nonlinksync extends Mage_Core_Model_Mysql4_Abstract {
    public function _construct() {
        $this->_init('linksynceparcel/nonlinksync', 'id');
    }
}