<?php
class Linksync_Linksynceparcel_Model_Mysql4_Manifest_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init('linksynceparcel/manifest');
    }
}