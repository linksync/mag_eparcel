<?php
class Linksync_Linksynceparcel_Model_System_Config_Source_Classification extends Mage_Core_Model_Abstract
{
	static public function toOptionArray() {
		 return array(
            array('value' => '991', 'label' => Mage::helper('linksynceparcel')->__('Other')),
            array('value' => '31', 'label' => Mage::helper('linksynceparcel')->__('Gift')),
  		 	array('value' => '32', 'label' => Mage::helper('linksynceparcel')->__('Commercial Sample')),
			array('value' => '91', 'label' => Mage::helper('linksynceparcel')->__('Document')),
        );
    }
}