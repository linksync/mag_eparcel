<?php
class Linksync_Linksynceparcel_Model_System_Config_Source_Expresspost extends Mage_Core_Model_Abstract
{
	static public function toOptionArray() {
		return array(
            array('value' => 'A4-3pp_1', 'label' => Mage::helper('linksynceparcel')->__('A4 plain')),
  		 	array('value' => 'A4-3pp_0', 'label' => Mage::helper('linksynceparcel')->__('A4 pre-printed')),
			array('value' => 'THERMAL LABEL-1PP_1', 'label' => Mage::helper('linksynceparcel')->__('Single plain')),
			array('value' => 'THERMAL LABEL-1PP_0', 'label' => Mage::helper('linksynceparcel')->__('Single pre-printed'))
        );
    }
}