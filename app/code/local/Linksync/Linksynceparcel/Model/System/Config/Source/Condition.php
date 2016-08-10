<?php
class Linksync_Linksynceparcel_Model_System_Config_Source_Condition extends Mage_Core_Model_Abstract
{
	static public function toOptionArray() {
		 return array(
			array('value' => 'package_weight', 'label' => Mage::helper('adminhtml')->__('Weight vs Destination')),
		);
    }
}