<?php
class Linksync_Linksynceparcel_Model_Mysql4_Manifest extends Mage_Core_Model_Mysql4_Abstract {
    public function _construct() {
        $this->_init('linksynceparcel/manifest', 'manifest_id');
    }
}