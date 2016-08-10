<?php
class Linksync_Linksynceparcel_Model_System_Config_Source_Declare extends Mage_Core_Model_Abstract
{
	static public function toOptionArray() {
		 return array(
            array('value' => '0', 'label' => Mage::helper('linksynceparcel')->__('Order Value')),
            array('value' => '1', 'label' => Mage::helper('linksynceparcel')->__('Order Value with Maximum')),
  		 	array('value' => '2', 'label' => Mage::helper('linksynceparcel')->__('Fixed Value'))
        );
    }
}