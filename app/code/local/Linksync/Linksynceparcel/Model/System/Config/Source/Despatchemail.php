<?php
class Linksync_Linksynceparcel_Model_System_Config_Source_Despatchemail extends Mage_Core_Model_Abstract
{
	static public function toOptionArray() {
		 return array(
            array('value' => 'none', 'label' => Mage::helper('linksynceparcel')->__('None')),
            array('value' => 'despatch-email', 'label' => Mage::helper('linksynceparcel')->__('Despatch Email')),
  		 	array('value' => 'track-advice', 'label' => Mage::helper('linksynceparcel')->__('Track Advice')),
        );
    }
}