<?php
class Linksync_Linksynceparcel_Model_System_Config_Source_Mode extends Mage_Core_Model_Abstract
{
	static public function toOptionArray() {
		 return array(
            array('value' => 2, 'label' => Mage::helper('linksynceparcel')->__('Test')),
            array('value' => 1, 'label' => Mage::helper('linksynceparcel')->__('Live')),
        );
    }
}