<?php
class Linksync_Linksynceparcel_Model_System_Config_Source_Labelformat extends Mage_Core_Model_Abstract
{
	static public function toOptionArray() {
		 return array(
            array('value' => 'sp', 'label' => Mage::helper('linksynceparcel')->__('sp')),
            array('value' => 'spp', 'label' => Mage::helper('linksynceparcel')->__('spp')),
  		 	array('value' => 'mp', 'label' => Mage::helper('linksynceparcel')->__('mp')),
			array('value' => 'mpp', 'label' => Mage::helper('linksynceparcel')->__('mpp')),
        );
    }
}