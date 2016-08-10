<?php
class Linksync_Linksynceparcel_Model_System_Config_Source_Productunit extends Mage_Core_Model_Abstract
{
	static public function toOptionArray() {
		 return array(
            array('value' => 'kgs', 'label' => Mage::helper('linksynceparcel')->__('Kgs')),
            array('value' => 'grams', 'label' => Mage::helper('linksynceparcel')->__('Grams')),
        );
    }
}