<?php
class Linksync_Linksynceparcel_Model_System_Config_Source_State extends Mage_Core_Model_Abstract
{
	static public function toOptionArray() {
		 return array(
            array('value' => '', 'label' => Mage::helper('linksynceparcel')->__('Please select')),
            array('value' => 'ACT', 'label' => Mage::helper('linksynceparcel')->__('ACT')),
  		 	array('value' => 'NSW', 'label' => Mage::helper('linksynceparcel')->__('NSW')),
			array('value' => 'NT', 'label' => Mage::helper('linksynceparcel')->__('NT')),
			array('value' => 'QLD', 'label' => Mage::helper('linksynceparcel')->__('QLD')),
  		 	array('value' => 'SA', 'label' => Mage::helper('linksynceparcel')->__('SA')),
			array('value' => 'TAS', 'label' => Mage::helper('linksynceparcel')->__('TAS')),
			array('value' => 'VIC', 'label' => Mage::helper('linksynceparcel')->__('VIC')),
			array('value' => 'WA', 'label' => Mage::helper('linksynceparcel')->__('WA')),
        );
    }
}