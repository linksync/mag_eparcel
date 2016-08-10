<?php
class Linksync_Linksynceparcel_Model_Notification extends Mage_AdminNotification_Model_Feed
{
	public function notify()
	{
		$active = (int)Mage::getStoreConfig('carriers/linksynceparcel/active');
		if($active == 1)
		{
			$model = Mage::getModel('linksynceparcel/notification');
			$model->checkUpdate();
		}
	}
	
	public function checkUpdate()
    {
        if ($this->getLastUpdate() > time()) {
            return $this;
        }
		
		$currentVersion = Mage::helper('linksynceparcel')->getExtensionVersion();
		$result = Mage::getModel('linksynceparcel/api')->getVersionNumber();
		if($result)
		{
			$latestVersion = $result->version_number;
			
			if( intval(str_replace('.','',$currentVersion)) < intval(str_replace('.','',$latestVersion)) )
			{
				$feedData = array();
				$feedData[] = array(
					'severity'      => 3,
					'date_added'    => $this->getDate(date('D M d h:i:s Y')),
					'title'         => 'New version of linksync eParcel - '.$latestVersion.' is available!',
					'description'   => 'New version of linksync eParcel - '.$latestVersion.' available, please contact linksync to get the latest package. Visit http://www.linksync.com/help/releases-eparcel-magento to learn more. ',
					'url'           => 'http://www.linksync.com/help/releases-eparcel-magento',
				);
		
				if ($feedData) {
					Mage::getModel('adminnotification/inbox')->parse(array_reverse($feedData));
				}
		
				$this->setLastUpdate();
			}
		}
        return $this;
    }
}