<?php

class Linksync_Linksynceparcel_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		$resource = Mage::getSingleton('core/resource');
	    $writeConnection = $resource->getConnection('core_write');
		
		$isupgraded = Mage::helper('linksynceparcel')->isupgraded();
		
		if(!$isupgraded) {
			$table = $resource->getTableName('linksync_linksynceparcel_international_fields');
			$query = "CREATE TABLE IF NOT EXISTS `". $table ."` (
						`order_id` int(11) NOT NULL DEFAULT '0',
						`consignment_number` varchar(128) NOT NULL,
						`add_date` varchar(40) NOT NULL,
						`modify_date` varchar(40) NOT NULL,
						`insurance` tinyint(1) NOT NULL DEFAULT '0',
						`insurance_value` varchar(255) NOT NULL,
						`export_declaration_number` varchar(255) NOT NULL,
						`declared_value` tinyint(1) NOT NULL DEFAULT '0',
						`declared_value_text` varchar(255) NOT NULL,
						`has_commercial_value` tinyint(1) NOT NULL DEFAULT '0', 
						`product_classification` int(11) NOT NULL DEFAULT '991',
						`product_classification_text` varchar(255) NOT NULL,
						`country_origin` varchar(255) DEFAULT NULL,
						`hs_tariff` varchar(255) DEFAULT NULL,
						`default_contents` varchar(255) DEFAULT NULL,
						`ship_country` varchar(255) DEFAULT NULL
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
			$writeConnection->query($query);
			
			$table = $resource->getTableName('linksync_linksynceparcel_consignment');
			$query = "ALTER TABLE `". $table ."` 
					ADD `delivery_country` varchar(10),
					ADD `customdocs` varchar(255),
					ADD `is_customdocs_printed` tinyint(1) NOT NULL DEFAULT '0' AFTER `is_label_printed`,
					ADD `delivery_instruction` varchar(300),
					ADD `safe_drop` tinyint(1);";
			$writeConnection->query($query);
		}
		
		echo 'Upgrade complete! Hit the back button to return to the previous screen.';
    }
	
	public function ExportTableRatesAction()
	{
		$extensionPath = Mage::helper('linksynceparcel')->getExtensionPath();
		$etcPath = $extensionPath.DS.'etc';
		$csvFilename = $etcPath.DS.'linksync_eparcel_tablerate.csv';
		if(!file_exists($csvFilename))
		{
			$csvFilename = $etcPath.DS.'linksync_eparcel_tablerate_default.csv';
		}
		
		$filename = 'linksync_eparcel_tablerate.csv';
		$content = array(
			'type'  => 'filename',
			'value' => $csvFilename,
			'rm'    => false 
		);

        $this->_prepareDownloadResponse($filename, $content);
	}
	
	public function updateLabelAsPrintedAction()
	{
		$consignmentNumber = $this->getRequest()->getParam('consignmentNumber');
		$consignmentNumber = preg_replace('/[^0-9a-zA-Z]/', '', $consignmentNumber);
		$pos = strpos($consignmentNumber, 'int');
		if ($pos === false) {			
			Mage::helper('linksynceparcel')->updateConsignmentTable2($consignmentNumber,'is_label_printed', 1);
		} else {
			$consignmentNumber = str_replace('int', '', $consignmentNumber);
			Mage::helper('linksynceparcel')->updateConsignmentTable2($consignmentNumber,'is_customdocs_printed', 1);
		}
	}
	
	public function updateReturnLabelAsPrintedAction()
	{
		$consignmentNumber = $this->getRequest()->getParam('consignmentNumber');
		$consignmentNumber = preg_replace('/[^0-9a-zA-Z]/', '', $consignmentNumber);
		Mage::helper('linksynceparcel')->updateConsignmentTable2($consignmentNumber,'is_return_label_printed', 1);
	}
	
	public function sendlogAction()
	{
		try
		{
			if(!Mage::helper('linksynceparcel')->isZipArchiveInstalled())
			{
				throw new Exception('PHP ZipArchive extension is not enabled on your server, contact your web hoster to enable this extension.');
			}
			else
			{
				if(Mage::getModel('linksynceparcel/api')->sendLog())
				{
					$message =  'Log has been sent to LWS successfully.';
				}
				else
				{
					$message =  'Log failed to sent to LWS';
				}
				Mage::log('Send Log: '.$message, null, 'linksync_eparcel.log', true);
				echo $message;
			}
		}
		catch(Exception $e)
		{
			$message = $e->getMessage();
			Mage::log('Send Log: '.$message, null, 'linksync_eparcel.log', true);
			echo $message;
		}
	}
}