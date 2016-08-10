<?php
class Linksync_Linksynceparcel_Model_System_Config_Backend_Import extends Mage_Core_Model_Config_Data
{
	public function _afterSave()
	{
		Mage::getResourceModel('linksynceparcel/carrier_linksynceparcel')->uploadAndImport($this);
	}
}