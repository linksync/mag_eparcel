<?php
class Linksync_Linksynceparcel_Model_System_Config_Source_Calculatemethod extends Mage_Core_Model_Abstract
{
	static public function toOptionArray() {
		 return array(
            array('value' => 'F', 'label' => Mage::helper('linksynceparcel')->__('Fixed')),
            array('value' => 'P', 'label' => Mage::helper('linksynceparcel')->__('Percentage')),
        );
    }
}