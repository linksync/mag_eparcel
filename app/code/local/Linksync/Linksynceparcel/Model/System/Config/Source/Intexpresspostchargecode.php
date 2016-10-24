<?php
class Linksync_Linksynceparcel_Model_System_Config_Source_Intexpresspostchargecode extends Mage_Core_Model_Abstract
{
	static public function toOptionArray() {
		return Mage::helper('linksynceparcel')->getChargecodesByService('int_express_post');
    }
}