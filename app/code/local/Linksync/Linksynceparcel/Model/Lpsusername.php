<?php
class Linksync_Linksynceparcel_Model_Lpsusername extends Mage_Core_Model_Config_Data
{
	public function _beforeSave()
    {
		$lpsusername = $_POST['groups']['linksynceparcel']['fields']['lps_username']['value'];
		$xpldlps = explode('@', $lpsusername);
		if($xpldlps[1] != 'auspost.com.au') {
			Mage::throwException('LPS username is invalid. Should be similar to "6f4d9019-cxx4-4e5c-x786-0753b97d903e@auspost.com.au"');
		}
    }
}
