<?php
class Linksync_Linksynceparcel_Model_Manifest extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('linksynceparcel/manifest');
    }
}