<?php
if(!defined('LINKSYNCEPARCEL_DEBUG'))
	define('LINKSYNCEPARCEL_DEBUG',1);
define('LINKSYNC_SAVE_CONSIGNMENT', 0);
class Linksync_Linksynceparcel_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getExtensionPath()
	{
		$codePath = Mage::getBaseDir('code');
		$extensionPath = $codePath.DS.'local'.DS.'Linksync'.DS.'Linksynceparcel';
		return $extensionPath;
	}
	
	public function collectShippingData($method)
	{
		return explode('_',$method);
	}
	
	public function setAuthority($order_id, $note)
	{
		$resource = Mage::getSingleton('core/resource');
	    $writeConnection = $resource->getConnection('core_write');
	    $table = $resource->getTableName('linksync_linksynceparcel_authority');

		$query = "INSERT {$table} SET order_id = '{$order_id}', instructions='". $note ."'";
		return $writeConnection->query($query);
	}
	
	public function getInstructions($order)
	{
		$incrementId = $order->getOriginalIncrementId();
		if($incrementId)
		{
			$orgOrder = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
			$order_id = $orgOrder->getId();
	  	}
		else
		{
			$order_id = $order->getId();
		}
		
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('linksync_linksynceparcel_authority');

		$query = "select instructions from {$table} where order_id = '{$order_id}'";
		return $readConnection->fetchOne($query);
	}
	
	public function isOrderAddressValid($order_id, $force=false)
	{
		$order = Mage::getModel('sales/order')->load($order_id);
		if(!$force && $order->getIsAddressValid())
			return 1;
		$address = $order->getShippingAddress();
		$status = Mage::getSingleton('linksynceparcel/api')->isAddressValid($address);
		if($status == 1)
		{
			$order->setIsAddressValid(1);
		}
		else
		{
			$order->setIsAddressValid(0);
		}
		$order->save();
		return $status;
	}
	
	/*public function isOrderAddressValid($order_id, $force=false)
	{
		$order = Mage::getModel('sales/order')->load($order_id);
		if(!$force && $order->getIsAddressValid())
			return 1;
		$address = $order->getShippingAddress();
		$status = Mage::getSingleton('linksynceparcel/api')->isAddressValid($address);
		if($status == 1)
		{
			$this->updateOrderTable($order_id,'is_address_valid',1); 
		}
		else
		{
			$this->updateOrderTable($order_id,'is_address_valid',0);
		}
		$order->save();
		return $status;
	}*/
	
	public function updateOrderTable($order_id,$columnName, $value)
	{
		$resource = Mage::getSingleton('core/resource');
	    $writeConnection = $resource->getConnection('core_write');
	    $table = $resource->getTableName('sales_flat_order');

		$query = "UPDATE {$table} SET {$columnName} = '{$value}' WHERE entity_id='{$order_id}'";
		$writeConnection->query($query);
	}
	
	public function getStoreConfig($path)
	{
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('core_config_data');
    
    	$query = 'SELECT value FROM ' . $table . ' WHERE path like "%'.$path.'%" LIMIT 1';
		return $readConnection->fetchOne($query);
	}
	
	public function isSoapInstalled()
	{
		if(class_exists('SoapClient'))
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	
	public function isZipArchiveInstalled()
	{
		if(class_exists('ZipArchive'))
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
	
	public function prepareArticle($data,$order,$consignment_number='')
	{
		$articleData = $this->prepareArticleData($data,$order,$consignment_number);
		$content = $articleData['content'];
		$chargeCode = $articleData['charge_code'];
		
		$id_order = $this->getIncrementId($order);
		
		if(!empty($content) > 0)
		{
			if(LINKSYNCEPARCEL_DEBUG == 1 && LINKSYNC_SAVE_CONSIGNMENT == 1)
			{
				$filename = $id_order.'.xml';
				$handle = fopen($this->getTemplatePath().DS.'article'.DS.$filename,'w+');
				fwrite($handle, $content);
			}
		}
		return array('content' => $content, 'charge_code' => $chargeCode);
	}
	
	public function prepareArticleBulk($data,$order)
	{
		$articleData = $this->prepareArticleDataBulk($data,$order);
		$content = $articleData['content'];
		$chargeCode = $articleData['charge_code'];
		
		$id_order = $this->getIncrementId($order);
		
		if(!empty($content) > 0)
		{
			if(LINKSYNCEPARCEL_DEBUG == 1 && LINKSYNC_SAVE_CONSIGNMENT == 1)
			{
				$filename = $id_order.'.xml';
				$handle = fopen($this->getTemplatePath().DS.'article'.DS.$filename,'w+');
				fwrite($handle, $content);
			}
		}
		return array('content' => $content, 'charge_code' => $chargeCode);
	}
	
	public function cleanup(&$item, $key)
	{
		$item = trim(preg_replace('/\s\s+/', ' ', $item));
		$item = str_replace(LINKSYNC_SEPARATOR, LINKSYNC_REPLACE_SEPARATOR, $item);
	}
	
	public function getTemplatePath()
	{
		$codePath = Mage::getBaseDir('code');
		$extensionPath = $codePath.DS.'local'.DS.'Linksync'.DS.'Linksynceparcel';
		$etcPath = $extensionPath.DS.'etc';
		return $etcPath;
	}
	
	public function prepareModifiedArticle($order,$consignment_number)
	{
		$articleData = $this->prepareModifiedArticleData($order,$consignment_number);
		$content = $articleData['content'];
		$chargeCode = $articleData['charge_code'];
		$id_order = $this->getIncrementId($order);
		
		if(!empty($content) > 0)
		{
			if(LINKSYNCEPARCEL_DEBUG == 1 && LINKSYNC_SAVE_CONSIGNMENT == 1)
			{
				$filename = $id_order.'.xml';
				$handle = fopen($this->getTemplatePath().DS.'article'.DS.$filename,'w+');
				fwrite($handle, $content);
			}
		}
		return array('content' => $content, 'charge_code' => $chargeCode);
	}
	
	public function prepareUpdateArticle($data,$order,$consignmentNumber)
	{
		$articleData = $this->prepareUpdateArticleData($data,$order,$consignmentNumber);
		$content = $articleData['content'];
		$chargeCode = $articleData['charge_code'];
		$id_order = $this->getIncrementId($order);
		
		if(!empty($content) > 0)
		{
			if(LINKSYNCEPARCEL_DEBUG == 1 && LINKSYNC_SAVE_CONSIGNMENT == 1)
			{
				$filename = $id_order.'.xml';
				$handle = fopen($this->getTemplatePath().DS.'article'.DS.$filename,'w+');
				fwrite($handle, $content);
			}
		}
		return array('content' => $content, 'charge_code' => $chargeCode);
	}
	
	public function prepareAddArticle($data,$order,$consignmentNumber)
	{
		$articleData = $this->prepareAddArticleData($data,$order,$consignmentNumber);
		$content = $articleData['content'];
		$chargeCode = $articleData['charge_code'];
		$id_order = $this->getIncrementId($order);
		
		if(!empty($content) > 0)
		{
			if(LINKSYNCEPARCEL_DEBUG == 1)
			{
				$filename = $id_order.'.xml';
				$handle = fopen($this->getTemplatePath().DS.'article'.DS.$filename,'w+');
				fwrite($handle, $content);
			}
		}
		return array('content' => $content, 'charge_code' => $chargeCode);
	}
		
	public function prepareReturnAddress($storeId)
	{
		$returnAddressLine2 = trim(Mage::getStoreConfig('carriers/linksynceparcel/return_address_line2', $storeId));
		if(!empty($returnAddressLine2))
		{
			$returnAddressLine2 = '<returnAddressLine2>'.trim($this->xmlData($returnAddressLine2)).'</returnAddressLine2>';
		}
		else
		{
			$returnAddressLine2 = '<returnAddressLine2 />';
		}
		
		$returnAddressLine3 = trim(Mage::getStoreConfig('carriers/linksynceparcel/return_address_line3', $storeId));
		if(!empty($returnAddressLine3))
		{
			$returnAddressLine3 = '<returnAddressLine3>'.trim($this->xmlData($returnAddressLine3)).'</returnAddressLine3>';
		}
		else
		{
			$returnAddressLine3 = '<returnAddressLine3 />';
		}
		
		$returnAddressLine4 = trim(Mage::getStoreConfig('carriers/linksynceparcel/return_address_line4', $storeId));
		if(!empty($returnAddressLine4))
		{
			$returnAddressLine4 = '<returnAddressLine4>'.trim($this->xmlData($returnAddressLine4)).'</returnAddressLine4>';
		}
		else
		{
			$returnAddressLine4 = '<returnAddressLine4 />';
		}
		
		$search = array(
			'[[returnAddressLine1]]',
			'[[returnName]]',
			'[[returnPostcode]]',
			'[[returnStateCode]]',
			'[[returnSuburb]]',
			'[[returnAddressLine2]]',
			'[[returnAddressLine3]]',
			'[[returnAddressLine4]]'
		);

		$replace = array(
			trim($this->xmlData(Mage::getStoreConfig('carriers/linksynceparcel/return_address_line1', $storeId))),
			trim($this->xmlData(Mage::getStoreConfig('carriers/linksynceparcel/return_address_name', $storeId))),
			trim(Mage::getStoreConfig('carriers/linksynceparcel/return_address_postcode', $storeId)),
			trim(Mage::getStoreConfig('carriers/linksynceparcel/return_address_statecode', $storeId)),
			trim($this->xmlData(Mage::getStoreConfig('carriers/linksynceparcel/return_address_suburb', $storeId))),
			trim($returnAddressLine2),
			trim($returnAddressLine3),
			trim($returnAddressLine4)
		);
		
		$template = file_get_contents($this->getTemplatePath().DS.'article-return-address-template.xml');
		return str_replace($search, $replace, $template);
	}	
	
	public function prepareArticleData($data,$order,$consignment_number="")
	{
		$deliveryAddress  = $order->getShippingAddress()->getData();
		$chargeCode = $this->getChargeCode($order,$consignment_number);
		$storeId = $order->getStoreId();
		$shipAddress = $order->getShippingAddress();
		$country = $shipAddress->getCountry();
		$total_weight = 0;

		$combinations = $this->getCombination($chargeCode);
		if($combinations) {
			$validateCombination = $this->validateCombination($data, $combinations, $chargeCode);
			if(is_array($validateCombination)) {
				return $validateCombination;
			}
		}

		if($country != 'AU') {
			$returnInternationalAddress = $this->prepareInternationalReturnAddress($storeId);
			$deliveryInternationalInfo = $this->prepareInternationalDeliveryAddress($deliveryAddress,$order,$data,$country);
			$articlesInternationalInfo = $this->prepareInternationalArticles($data, $order);
			$total_weight = $articlesInternationalInfo['total_weight'];
			
			$search = array(
				'[[articles]]',
				'[[DELIVERY-ADDRESS]]',
				'[[RETURN-ADDRESS]]',
				'[[ORDER-ID]]',
				'[[CHARGE-CODE]]'
			);
			
			$replace = array(
				$articlesInternationalInfo['info'],
				$deliveryInternationalInfo,
				$returnInternationalAddress,
				$this->getIncrementId($order),
				$chargeCode
			);
			$template = file_get_contents($this->getTemplatePath().DS.'international-articles-template.xml');
		} else {
			$returnAddress = $this->prepareReturnAddress($storeId);
			$deliveryInfo = $this->prepareDeliveryAddress($deliveryAddress,$order,$data);
			$articlesInfo = $this->prepareArticles($data, $order);
			$total_weight = $articlesInfo['total_weight'];
			
			$search = array(
				'[[articles]]',
				'[[RETURN-ADDRESS]]',
				'[[DELIVERY-ADDRESS]]',
				'[[CUSTOMER-EMAIL]]',
				'[[DELIVERY-SIGNATURE]]',
				'[[ORDER-ID]]',
				'[[CHARGE-CODE]]',
				'[[SHIPMENT-ID]]',
				'[[DANGER-GOODS]]',
				'[[printReturnLabels]]',
				'[[deliverPartConsignment]]',
				'[[cashToCollect]]',
				'[[cashToCollectAmount]]',
				'[[emailNotification]]',
				'[[safeDrop]]'
			);

				
			$replace = array(
				$articlesInfo['info'],
				$returnAddress,
				$deliveryInfo,
				$order->getCustomerEmail(),
				($data['delivery_signature_allowed'] ? 'true' : 'false'),
				$this->getIncrementId($order),
				$chargeCode,
				$order->getId(),
				($data['contains_dangerous_goods'] ? 'true' : 'false'),
				($data['print_return_labels'] ? 'true' : 'false'),
				($data['partial_delivery_allowed'] ? 'Y' : 'N'),
				(isset($data['cash_to_collect']) ? '<cashToCollect>Y</cashToCollect>' : '<cashToCollect>N</cashToCollect>'),
				(isset($data['cash_to_collect']) ? '<cashToCollectAmount>'.number_format($data['cash_to_collect'],2).'</cashToCollectAmount>' : ''),
				($data['email_notification'] ? 'Y' : 'N'),
				($data['safe_drop']==1 ? 'yes' : 'no')
			);
			$template = file_get_contents($this->getTemplatePath().DS.'articles-template.xml');
		}
		
		$articleData = str_replace($search, $replace, $template);
		return array('content' => $articleData, 'charge_code' => $chargeCode, 'total_weight' => $total_weight);
	}
	
	public function prepareOrderWeightArticleData($data,$order,$consignment_number='')
	{
		$deliveryAddress  = $order->getShippingAddress()->getData();
		
		$chargeCode = $this->getChargeCode($order,$consignment_number);
		$storeId = $order->getStoreId();
		$shipAddress = $order->getShippingAddress();
		$country = $shipAddress->getCountry();
		$total_weight = 0;

		$combinations = $this->getCombination($chargeCode);
		if($combinations) {
			$validateCombination = $this->validateCombination($data, $combinations, $chargeCode);
			if(is_array($validateCombination)) {
				return $validateCombination;
			}
		}

		if($country != 'AU') {
			$returnInternationalAddress = $this->prepareInternationalReturnAddress($storeId);
			$deliveryInternationalInfo = $this->prepareInternationalDeliveryAddress($deliveryAddress,$order,$data,$country);
			$articlesInternationalInfo = $this->prepareInternationalOrderWeightArticles($data, $order);
			$total_weight = $articlesInternationalInfo['total_weight'];
			
			$search = array(
				'[[articles]]',
				'[[DELIVERY-ADDRESS]]',
				'[[RETURN-ADDRESS]]',
				'[[ORDER-ID]]',
				'[[CHARGE-CODE]]'
			);
			
			$replace = array(
				$articlesInternationalInfo['info'],
				$deliveryInternationalInfo,
				$returnInternationalAddress,
				$this->getIncrementId($order),
				$chargeCode
			);
			$template = file_get_contents($this->getTemplatePath().DS.'international-articles-template.xml');
		} else {
			$returnAddress = $this->prepareReturnAddress($storeId);
			$deliveryInfo = $this->prepareDeliveryAddress($deliveryAddress,$order,$data);
			$articlesInfo = $this->prepareOrderWeightArticles($data, $order);
			$total_weight = $articlesInfo['total_weight'];
			
			$search = array(
				'[[articles]]',
				'[[RETURN-ADDRESS]]',
				'[[DELIVERY-ADDRESS]]',
				'[[CUSTOMER-EMAIL]]',
				'[[DELIVERY-SIGNATURE]]',
				'[[ORDER-ID]]',
				'[[CHARGE-CODE]]',
				'[[SHIPMENT-ID]]',
				'[[DANGER-GOODS]]',
				'[[printReturnLabels]]',
				'[[deliverPartConsignment]]',
				'[[cashToCollect]]',
				'[[cashToCollectAmount]]',
				'[[emailNotification]]'
			);

			$replace = array(
				$articlesInfo['info'],
				$returnAddress,
				$deliveryInfo,
				$order->getCustomerEmail(),
				($data['delivery_signature_allowed'] ? 'true' : 'false'),
				$this->getIncrementId($order),
				$chargeCode,
				$order->getId(),
				($data['contains_dangerous_goods'] ? 'true' : 'false'),
				($data['print_return_labels'] ? 'true' : 'false'),
				($data['partial_delivery_allowed'] ? 'Y' : 'N'),
				(isset($data['cash_to_collect']) ? '<cashToCollect>Y</cashToCollect>' : '<cashToCollect>N</cashToCollect>'),
				(isset($data['cash_to_collect']) ? '<cashToCollectAmount>'.number_format($data['cash_to_collect'],2).'</cashToCollectAmount>' : ''),
				($data['email_notification'] ? 'Y' : 'N')
			);
			$template = file_get_contents($this->getTemplatePath().DS.'articles-template.xml');
		}
		$articleData = str_replace($search, $replace, $template);
		return array('content' => $articleData, 'charge_code' => $chargeCode, 'total_weight' => $total_weight);
	}
	
	public function prepareArticleDataBulk($data,$order)
	{
		$deliveryAddress  = $order->getShippingAddress()->getData();
		
		$chargeCode = $this->getChargeCode($order);
		$storeId = $order->getStoreId();
		$shipAddress = $order->getShippingAddress();
		$country = $shipAddress->getCountry();
		$total_weight = 0;

		$combinations = $this->getCombination($chargeCode);
		if($combinations) {
			$validateCombination = $this->validateCombination($data, $combinations, $chargeCode);
			if(is_array($validateCombination)) {
				return $validateCombination;
			}
		}

		if($country != 'AU') {
			$returnInternationalAddress = $this->prepareInternationalReturnAddress($storeId);
			$deliveryInternationalInfo = $this->prepareInternationalDeliveryAddress($deliveryAddress,$order,$data,$country);
			$articlesInternationalInfo = $this->prepareInternationalArticles($data, $order, true);
			$total_weight = $articlesInternationalInfo['total_weight'];
			
			$search = array(
				'[[articles]]',
				'[[DELIVERY-ADDRESS]]',
				'[[RETURN-ADDRESS]]',
				'[[ORDER-ID]]',
				'[[CHARGE-CODE]]'
			);
			
			$replace = array(
				$articlesInternationalInfo['info'],
				$deliveryInternationalInfo,
				$returnInternationalAddress,
				$this->getIncrementId($order),
				$chargeCode
			);
			$template = file_get_contents($this->getTemplatePath().DS.'international-articles-template.xml');
		} else {
			$returnAddress = $this->prepareReturnAddress($storeId);
			$deliveryInfo = $this->prepareDeliveryAddress($deliveryAddress,$order,$data);
			$articlesInfo = $this->prepareArticlesBulk($data, $order);
			$total_weight = $articlesInfo['total_weight'];
			
			$search = array(
				'[[articles]]',
				'[[RETURN-ADDRESS]]',
				'[[DELIVERY-ADDRESS]]',
				'[[CUSTOMER-EMAIL]]',
				'[[DELIVERY-SIGNATURE]]',
				'[[ORDER-ID]]',
				'[[CHARGE-CODE]]',
				'[[SHIPMENT-ID]]',
				'[[DANGER-GOODS]]',
				'[[printReturnLabels]]',
				'[[deliverPartConsignment]]',
				'[[cashToCollect]]',
				'[[cashToCollectAmount]]',
				'[[emailNotification]]'
			);

			$replace = array(
				$articlesInfo['info'],
				$returnAddress,
				$deliveryInfo,
				$order->getCustomerEmail(),
				($data['delivery_signature_allowed'] ? 'true' : 'false'),
				$this->getIncrementId($order),
				$chargeCode,
				$order->getId(),
				($data['contains_dangerous_goods'] ? 'true' : 'false'),
				($data['print_return_labels'] ? 'true' : 'false'),
				($data['partial_delivery_allowed'] ? 'Y' : 'N'),
				(isset($data['cash_to_collect']) ? '<cashToCollect>Y</cashToCollect>' : '<cashToCollect>N</cashToCollect>'),
				(isset($data['cash_to_collect']) ? '<cashToCollectAmount>'.number_format($data['cash_to_collect'],2).'</cashToCollectAmount>' : ''),
				($data['email_notification'] ? 'Y' : 'N')
			);
			$template = file_get_contents($this->getTemplatePath().DS.'articles-template.xml');
		}
		$articleData = str_replace($search, $replace, $template);
		return array('content' => $articleData, 'charge_code' => $chargeCode, 'total_weight' => $total_weight);
	}
	
	public function prepareModifiedArticleData($order,$consignment_number='')
	{
		$deliveryAddress  = $order->getShippingAddress()->getData();
		$storeId = $order->getStoreId();
		$returnAddress = $this->prepareReturnAddress($storeId);
		$deliveryInfo = $this->prepareDeliveryAddress($deliveryAddress,$order);
		$articlesInfo = $this->prepareModifiedArticles($order,$consignment_number);
		
		$consignment = $this->getConsignment($order->getId(),$consignment_number);
		//$consignment['general_linksynceparcel_shipping_chargecode']
		$search = array(
			'[[articles]]',
			'[[RETURN-ADDRESS]]',
			'[[DELIVERY-ADDRESS]]',
			'[[CUSTOMER-EMAIL]]',
			'[[DELIVERY-SIGNATURE]]',
			'[[ORDER-ID]]',
			'[[CHARGE-CODE]]',
			'[[SHIPMENT-ID]]',
			'[[DANGER-GOODS]]',
			'[[printReturnLabels]]',
			'[[deliverPartConsignment]]',
			'[[cashToCollect]]',
  			'[[cashToCollectAmount]]',
			'[[emailNotification]]'
		);

		$chargeCode = $this->getChargeCode($order,$consignment_number);
		$instructions = Mage::helper('linksynceparcel')->getInstructions($order);
	
		$replace = array(
			$articlesInfo['info'],
			$returnAddress,
			$deliveryInfo,
			$order->getCustomerEmail(),
			($consignment['delivery_signature_allowed'] ? 'true' : 'false'),
			$this->getIncrementId($order),
			$chargeCode,
			$order->getId(),
			($consignment['contains_dangerous_goods'] ? 'true' : 'false'),
			($consignment['print_return_labels'] ? 'true' : 'false'),
			($consignment['partial_delivery_allowed'] ? 'Y' : 'N'),
			(!empty($consignment['cash_to_collect']) ? '<cashToCollect>Y</cashToCollect>' : '<cashToCollect>N</cashToCollect>'),
			(!empty($consignment['cash_to_collect']) ? '<cashToCollectAmount>'.number_format($data['cash_to_collect'],2).'</cashToCollectAmount>' : ''),
			($data['email_notification'] ? 'Y' : 'N')			
		);
		$template = file_get_contents($this->getTemplatePath().DS.'articles-template.xml');
		$content = str_replace($search, $replace, $template);
		return array('content' => $content, 'charge_code' => $chargeCode, 'total_weight' => $articlesInfo['total_weight']);
	}
	
	public function prepareUpdateArticleData($data, $order,$consignment_number='')
	{
		$deliveryAddress  = $order->getShippingAddress()->getData();
		$storeId = $order->getStoreId();
		$returnAddress = $this->prepareReturnAddress($storeId);
		$deliveryInfo = $this->prepareDeliveryAddress($deliveryAddress,$order,$data);
		$articlesInfo = $this->prepareUpdatedArticles($order,$data);
		
		$search = array(
			'[[articles]]',
			'[[RETURN-ADDRESS]]',
			'[[DELIVERY-ADDRESS]]',
			'[[CUSTOMER-EMAIL]]',
			'[[DELIVERY-SIGNATURE]]',
			'[[ORDER-ID]]',
			'[[CHARGE-CODE]]',
			'[[SHIPMENT-ID]]',
			'[[DANGER-GOODS]]',
			'[[printReturnLabels]]',
			'[[deliverPartConsignment]]',
			'[[cashToCollect]]',
  			'[[cashToCollectAmount]]',
			'[[emailNotification]]'
		);

		$chargeCode = $this->getChargeCode($order,$consignment_number);
		
		$replace = array(
			$articlesInfo['info'],
			$returnAddress,
			$deliveryInfo,
			$order->getCustomerEmail(),
			($data['delivery_signature_allowed'] ? 'true' : 'false'),
			$this->getIncrementId($order),
			$chargeCode,
			$order->getId(),
			($data['contains_dangerous_goods'] ? 'true' : 'false'),
			($data['print_return_labels'] ? 'true' : 'false'),
			($data['partial_delivery_allowed'] ? 'Y' : 'N'),
			(isset($data['cash_to_collect']) ? '<cashToCollect>Y</cashToCollect>' : '<cashToCollect>N</cashToCollect>'),
			(isset($data['cash_to_collect']) ? '<cashToCollectAmount>'.number_format($data['cash_to_collect'],2).'</cashToCollectAmount>' : ''),
			($data['email_notification'] ? 'Y' : 'N')
		);
		$template = file_get_contents($this->getTemplatePath().DS.'articles-template.xml');
		$content = str_replace($search, $replace, $template);
		return array('content' => $content, 'charge_code' => $chargeCode, 'total_weight' => $articlesInfo['total_weight']);
	}
	
	public function prepareAddArticleData($data, $order,$consignment_number='')
	{
		$deliveryAddress  = $order->getShippingAddress()->getData();
		$storeId = $order->getStoreId();
		$returnAddress = $this->prepareReturnAddress($storeId);
		$deliveryInfo = $this->prepareDeliveryAddress($deliveryAddress,$order,$data);
		$articlesInfo = $this->prepareAddArticles($order,$data);
		
		$search = array(
			'[[articles]]',
			'[[RETURN-ADDRESS]]',
			'[[DELIVERY-ADDRESS]]',
			'[[CUSTOMER-EMAIL]]',
			'[[DELIVERY-SIGNATURE]]',
			'[[ORDER-ID]]',
			'[[CHARGE-CODE]]',
			'[[SHIPMENT-ID]]',
			'[[DANGER-GOODS]]',
			'[[printReturnLabels]]',
			'[[deliverPartConsignment]]',
			'[[cashToCollect]]',
  			'[[cashToCollectAmount]]',
			'[[emailNotification]]'
		);

		$chargeCode = $this->getChargeCode($order,$consignment_number);
	
		$replace = array(
			$articlesInfo['info'],
			$returnAddress,
			$deliveryInfo,
			$order->getCustomerEmail(),
			($data['delivery_signature_allowed'] ? 'true' : 'false'),
			$this->getIncrementId($order),
			$chargeCode,
			$order->getId(),
			($data['contains_dangerous_goods'] ? 'true' : 'false'),
			($data['print_return_labels'] ? 'true' : 'false'),
			($data['partial_delivery_allowed'] ? 'Y' : 'N'),
			(isset($data['cash_to_collect']) ? '<cashToCollect>Y</cashToCollect>' : '<cashToCollect>N</cashToCollect>'),
			(isset($data['cash_to_collect']) ? '<cashToCollectAmount>'.number_format($data['cash_to_collect'],2).'</cashToCollectAmount>' : ''),
			($data['email_notification'] ? 'Y' : 'N')
		);
		$template = file_get_contents($this->getTemplatePath().DS.'articles-template.xml');
		$content = str_replace($search, $replace, $template);
		return array('content' => $content, 'charge_code' => $chargeCode, 'total_weight' => $articlesInfo['total_weight']);
	}
	
	public function getChargeCode($order, $consignmentNumber='')
	{
		$chargeCode = $this->getOrderChargeCode($order->getId(), $consignmentNumber);
		return $chargeCode;
	}
	
	public function getIncrementId($order)
	{
		$incrementId = $order->getOriginalIncrementId();
		if($incrementId == null || empty($incrementId) || !$incrementId)
		{
			$incrementId = $order->getIncrementId();
	  	}
		return $incrementId;
	}
	
	public function getNotes($order)
	{
		$note = '';
		$note = $order->getCustomerNote();
		
		/*$orderComments = $order->getAllStatusHistory();
		foreach ($orderComments as $comment) 
		{
			$note .= $comment->getData('comment').'<br/>';
		}*/
		return $note;
	}
	
	public function prepareDeliveryAddress($address,$order,$data=false)
	{
		$street = $address['street'];
		$street = explode("\n", $street);

		$street1 = '<deliveryAddressLine1/>';
		$street2 = '<deliveryAddressLine2/>';
		$street3 = '<deliveryAddressLine3/>';
		$street4 = '<deliveryAddressLine4/>';
		if(isset($street[0]) && !empty($street[0]))
		{
			$street1 = '<deliveryAddressLine1>'.$this->xmlData($street[0]).'</deliveryAddressLine1>';
		}
		if(isset($street[1]) && !empty($street[1]))
		{
			$street2 = '<deliveryAddressLine2>'.$this->xmlData($street[1]).'</deliveryAddressLine2>';
		}
		if(isset($street[2]) && !empty($street[2]))
		{
			$street3 = '<deliveryAddressLine3>'.$this->xmlData($street[2]).'</deliveryAddressLine3>';
		}
		if(isset($street[3]) && !empty($street[3]))
		{
			$street4 = '<deliveryAddressLine4>'.$this->xmlData($street[3]).'</deliveryAddressLine4>';
		}
		
		$city = $address['city'];
		$state = 'NA';
		if($address['region'])
		{
			$state = Mage::helper('linksynceparcel')->getRegion($address['region_id']);
		}
		$postalCode = $address['postcode'];
		$company = empty($address['company']) ? '<deliveryCompanyName/>' : '<deliveryCompanyName>'.$this->xmlData($address['company']).'</deliveryCompanyName>';
		$firstname = $address['firstname'].' '.$address['lastname'];
		$email = $address['email'];
		$phone = $address['telephone'];
		$phonestr = $phone;
		$phone = $this->getValidPhoneNumber($phone);
		if(!empty($phone)) {
			$withplus = '';
			$strposphone = strpos($phone, '+');
			if($strposphone !== false) {
				$withplus = '+';
			}
			$phone = preg_replace('/[^0-9]/s', '', $phone);
			$phonestr = $withplus . $phone;
		}
		
		$instructions = $data['delivery_instruction'];
		
		$search = array(
			'[[deliveryAddressLine1]]',
			'[[deliveryAddressLine2]]',
			'[[deliveryAddressLine3]]',
			'[[deliveryAddressLine4]]',
			'[[deliveryCompanyName]]',
			'[[deliveryEmailAddress]]',
			'[[deliveryInstructions]]',
			'[[deliveryName]]',
			'[[deliveryPhoneNumber]]',
			'[[deliveryPostcode]]',
			'[[deliveryStateCode]]',
			'[[deliverySuburb]]'
		);

		$replace = array(
			trim($street1),
			trim($street2),
			trim($street3),
			trim($street4),
			trim($company),
			trim($email),
			($instructions ? '<deliveryInstructions>'.$this->xmlData(($instructions)).'</deliveryInstructions>' : '<deliveryInstructions />'),
			trim($this->xmlData($firstname)),
			trim($phonestr),
			trim($postalCode),
			trim($state),
			trim($this->xmlData($city))
		);
		
		$template = file_get_contents($this->getTemplatePath().DS.'article-delivery-address-template.xml');
		return str_replace($search, $replace, $template);
	}
	
	public function prepareArticles($data, $order,$consignment_number='',$international=false)
	{
		$articlesInfo = '';
		
		$total_weight = 0;
		
		$number_of_articles = $data['number_of_articles'];
		$start_index = $data['start_index'];
		$end_index = $data['end_index'];
		$articles = array();
		
		for($i=$start_index;$i<=$end_index;$i++)
		{
			if($data['articles_type'] == 'Custom')
			{
        				$article = $data['article'.$i];
			}
			else
			{
				$articles_type = $data['articles_type'];
				$articles = explode('<=>',$articles_type);
				
				$article = array();
				$article['description'] = trim($articles[0]);
				$article['weight'] = $articles[1];
				$article['height'] = trim($articles[2]);
				$article['width'] = trim($articles[3]);
				$article['length'] = trim($articles[4]);
				
				$use_order_total_weight = (int)Mage::getStoreConfig('carriers/linksynceparcel/use_order_total_weight');
				if($use_order_total_weight == 1)
				{
					$weight = Mage::helper('linksynceparcel')->getOrderWeight($order);
					$weightPerArticle = Mage::helper('linksynceparcel')->getAllowedWeightPerArticle();
					if($weight == 0)
					{
						$default_article_weight = Mage::getStoreConfig('carriers/linksynceparcel/default_article_weight');
						if($default_article_weight)
						{
							$weight = $default_article_weight;
						}
					}
					$exactArticles = (int)($weight / $weightPerArticle);
					$totalArticles = $exactArticles;
					$reminderWeight = fmod ($weight, $weightPerArticle);
					if($reminderWeight > 0)
					{
						$totalArticles++;
					}
					
					if($totalArticles == 0)
					{
						$totalArticles = 1;
					}
					
					if($weight > $weightPerArticle)
					{
						$weight = $weightPerArticle;
					}
					if($reminderWeight > 0 && $i == $totalArticles)
					{
						$weight = $reminderWeight;
					}
					$article['weight'] = $weight;
				}
			}
			
			if($data['edit_order_weight'] == 1) {
				$article['weight'] = $data['default_order_weight'];
			}
			
			$article['weight'] = $this->roundoff_number($article['weight'],2);
			$total_weight += $article['weight'];
			$article['weight'] = $this->calculateWeightDefault($article['weight']);
			
			if($international) {
				$search = array(
					'[[articleDescription]]',
					'[[actualWeight]]',
					'[[width]]',
					'[[height]]',
					'[[length]]'
				);

				$replace = array(
					$this->xmlData($article['description']),
					trim($article['weight']),
					trim($article['width']),
					trim($article['height']),
					trim($article['length'])
				);
				
				$template = file_get_contents($this->getTemplatePath().DS.'international-article-template.xml');
			} else {

				$search = array(
					'[[actualWeight]]',
					'[[articleDescription]]',
					'[[height]]',
					'[[length]]',
					'[[width]]',
					'[[isTransitCoverRequired]]',
					'[[transitCoverAmount]]',
					'[[articleNumber]]'
				);
			
			
				$replace = array(
					trim($article['weight']),
					$this->xmlData($article['description']),
					0,
					0,
					'<width>'. 0 .'</width>',
					($data['transit_cover_required'] ? 'Y' : 'N'),
					($data['transit_cover_required'] ? $data['transit_cover_amount'] : 0),
					(isset($article['article_number']) ? '<articleNumber>'.$article['article_number'].'</articleNumber>' : '')
				);
				
				$template = file_get_contents($this->getTemplatePath().DS.'article-template.xml');
			}
			$articlesInfo .= str_replace($search, $replace, $template);
		}
		return array('info' => $articlesInfo, 'total_weight' => $total_weight);
	}
	
	public function prepareOrderWeightArticles($data, $order,$consignment_number='',$isInternational=false)
	{
		$articlesInfo = '';
		
		$number_of_articles = $data['number_of_articles'];
		$start_index = $data['start_index'];
		$end_index = $data['end_index'];
		$articles = array();
		
		$totalWeight = 0;
		
		for($i=$start_index;$i<=$end_index;$i++)
		{
			if($data['articles_type'] == 'Custom')
			{
        		$article = $data['article'.$i];
			}
			else
			{
				$articles_type = $data['articles_type'];
				$articles = explode('<=>',$articles_type);
				
				$article = array();
				$article['description'] = $articles[0];
				$article['weight'] = $articles[1];
				$article['height'] = trim($articles[2]);
				$article['width'] = trim($articles[3]);
				$article['length'] = trim($articles[4]);
			}
			
			if($data['edit_order_weight'] == 1) {
				$article['weight'] = $data['default_order_weight'];
			}
			
			$article['weight'] = $this->roundoff_number($article['weight'],2);
			$totalWeight += $article['weight'];

			$article['weight'] = $this->calculateWeightDefault($article['weight']);
				
			if($isInternational) {
				$search = array(
					'[[articleDescription]]',
					'[[actualWeight]]',
					'[[width]]',
					'[[height]]',
					'[[length]]'
				);

				$replace = array(
					$this->xmlData(trim($article['description'])),
					trim($article['weight']),
					0,
					0,
					0
				);
				
				$template = file_get_contents($this->getTemplatePath().DS.'international-article-template.xml');
			} else {
				$search = array(
					'[[actualWeight]]',
					'[[articleDescription]]',
					'[[height]]',
					'[[length]]',
					'[[width]]',
					'[[isTransitCoverRequired]]',
					'[[transitCoverAmount]]',
					'[[articleNumber]]'
				);
				$replace = array(
					$article['weight'],
					$this->xmlData($article['description']),
					0,
					0,
					'<width>0</width>',
					($data['transit_cover_required'] ? 'Y' : 'N'),
					($data['transit_cover_required'] ? $data['transit_cover_amount'] : 0),
					(isset($article['article_number']) ? '<articleNumber>'.$article['article_number'].'</articleNumber>' : '')
				);
				
				$template = file_get_contents($this->getTemplatePath().DS.'article-template.xml');
			}
			$articlesInfo .= str_replace($search, $replace, $template);
		}
		return array('info' => $articlesInfo, 'total_weight' => $totalWeight);
	}
	
	public function prepareArticlesBulk($data, $order, $international=false)
	{
		$total_weight = 0;
		$articlesInfo = '';
		$articles_type = $data['articles_type'];
		if($articles_type == 'order_weight')
		{
			$weight = Mage::helper('linksynceparcel')->getOrderWeight($order);
			if($weight == 0)
			{
				$default_article_weight = Mage::getStoreConfig('carriers/linksynceparcel/default_article_weight');
				if($default_article_weight)
				{
					$weight = $default_article_weight;
				}
			}
			$weightPerArticle = Mage::helper('linksynceparcel')->getAllowedWeightPerArticle();
			$exactArticles = (int)($weight / $weightPerArticle);
			$totalArticles = $exactArticles;
			$reminderWeight = fmod ($weight, $weightPerArticle);
			if($reminderWeight > 0)
			{
				$totalArticles++;
			}
			
			if($totalArticles == 0)
			{
				$totalArticles = 1;
			}
			
			for($i=1;$i<=$totalArticles;$i++)
			{
				$article = array();
				$article['description'] = 'Article '.$i;
				
				if($reminderWeight > 0 && $i == $totalArticles)
				{
					$article['weight'] = $reminderWeight;
				}
				else
				{
					$article['weight'] = $weightPerArticle;
				}
				$article['height'] = 0;
				$article['length'] = 0;
				$article['width'] = 0;
				
				$article['weight'] = $this->roundoff_number($article['weight'],2);
				$total_weight += $article['weight'];

				$article['weight'] = $this->calculateWeightDefault($article['weight']);
				
				if($international) {
					$search = array(
						'[[articleDescription]]',
						'[[actualWeight]]',
						'[[width]]',
						'[[height]]',
						'[[length]]'
					);

					$replace = array(
						$this->xmlData(trim($article['description'])),
						trim($article['weight']),
						$this->zeroIfEmpty($article['width']),
						$this->zeroIfEmpty($article['height']),
						$this->zeroIfEmpty($article['length'])
					);
					
					$template = file_get_contents($this->getTemplatePath().DS.'international-article-template.xml');
				} else {
					$search = array(
						'[[actualWeight]]',
						'[[articleDescription]]',
						'[[height]]',
						'[[length]]',
						'[[width]]',
						'[[isTransitCoverRequired]]',
						'[[transitCoverAmount]]',
						'[[articleNumber]]'
					);
					
					$replace = array(
						trim($article['weight']),
						$this->xmlData($article['description']),
						$this->zeroIfEmpty($article['height']),
						$this->zeroIfEmpty($article['length']),
						'<width>'. $this->zeroIfEmpty($article['width']) .'</width>',
						($data['transit_cover_required'] ? 'Y' : 'N'),
						($data['transit_cover_required'] ? $data['transit_cover_amount'] : 0),
						''
					);
					
					$template = file_get_contents($this->getTemplatePath().DS.'article-template.xml');
				}
				$articlesInfo .= str_replace($search, $replace, $template);
			}
		}
		else
		{
			$articles = explode('<=>',$articles_type);
			
			$use_order_total_weight = (int)Mage::getStoreConfig('carriers/linksynceparcel/use_order_total_weight');
			if($use_order_total_weight == 1)
			{
				$weight = Mage::helper('linksynceparcel')->getOrderWeight($order);
				if($weight == 0)
				{
					$default_article_weight = Mage::getStoreConfig('carriers/linksynceparcel/default_article_weight');
					if($default_article_weight)
					{
						$weight = $default_article_weight;
					}
				}
				$weightPerArticle = Mage::helper('linksynceparcel')->getAllowedWeightPerArticle();
				$exactArticles = (int)($weight / $weightPerArticle);
				$totalArticles = $exactArticles;
				$reminderWeight = fmod ($weight, $weightPerArticle);
				if($reminderWeight > 0)
				{
					$totalArticles++;
				}
				
				if($totalArticles == 0)
				{
					$totalArticles = 1;
				}
				
				for($i=1;$i<=$totalArticles;$i++)
				{
					$article = array();
					$article['description'] = $articles[0];
					$article['height'] = trim($articles[2]);
					$article['width'] = trim($articles[3]);
					$article['length'] = trim($articles[4]);
					
					if($reminderWeight > 0 && $i == $totalArticles)
					{
						$article['weight'] = $reminderWeight;
					}
					else
					{
						$article['weight'] = $weightPerArticle;
					}
					
					$article['weight'] = $this->roundoff_number($article['weight'],2);
					$total_weight += $article['weight'];

					$article['weight'] = $self->calculateWeightDefault($article['weight']);
					
					if($international) {
						$search = array(
							'[[articleDescription]]',
							'[[actualWeight]]',
							'[[width]]',
							'[[height]]',
							'[[length]]'
						);

						$replace = array(
							$this->xmlData(trim($article['description'])),
							trim($article['weight']),
							$this->zeroIfEmpty($article['width']),
							$this->zeroIfEmpty($article['height']),
							$this->zeroIfEmpty($article['length'])
						);
						
						$template = file_get_contents($this->getTemplatePath().DS.'international-article-template.xml');
					} else {
						
						$search = array(
							'[[actualWeight]]',
							'[[articleDescription]]',
							'[[height]]',
							'[[length]]',
							'[[width]]',
							'[[isTransitCoverRequired]]',
							'[[transitCoverAmount]]',
							'[[articleNumber]]'
						);
					
						$replace = array(
							trim($article['weight']),
							$this->xmlData($article['description']),
							$this->zeroIfEmpty($article['height']),
							$this->zeroIfEmpty($article['length']),
							'<width>'. $this->zeroIfEmpty($article['width']) .'</width>',
							($data['transit_cover_required'] ? 'Y' : 'N'),
							($data['transit_cover_required'] ? $data['transit_cover_amount'] : 0),
							''
						);
						
						$template = file_get_contents($this->getTemplatePath().DS.'article-template.xml');
					}
					$articlesInfo .= str_replace($search, $replace, $template);
				}
			}
			else
			{
				$article = array();
				$article['description'] = $articles[0];
				$article['weight'] = $articles[1];
				$article['height'] = trim($articles[2]);
				$article['length'] = trim($articles[3]);
				$article['width'] = trim($articles[4]);
				
				$article['weight'] = $this->roundoff_number($article['weight'],2);
				$total_weight += $article['weight'];

				$article['weight'] = $this->calculateWeightDefault($article['weight']);
				
				if($international) {
					$search = array(
						'[[articleDescription]]',
						'[[actualWeight]]',
						'[[width]]',
						'[[height]]',
						'[[length]]'
					);

					$replace = array(
						$this->xmlData(trim($article['description'])),
						trim($article['weight']),
						$this->zeroIfEmpty($article['width']),
						$this->zeroIfEmpty($article['height']),
						$this->zeroIfEmpty($article['length'])
					);
					
					$template = file_get_contents($this->getTemplatePath().DS.'international-article-template.xml');
				} else {
					$search = array(
						'[[actualWeight]]',
						'[[articleDescription]]',
						'[[height]]',
						'[[length]]',
						'[[width]]',
						'[[isTransitCoverRequired]]',
						'[[transitCoverAmount]]',
						'[[articleNumber]]'
					);
					
					$replace = array(
						trim($article['weight']),
						$this->xmlData($article['description']),
						$this->zeroIfEmpty($article['height']),
						$this->zeroIfEmpty($article['length']),
						'<width>'. $this->zeroIfEmpty($article['width']) .'</width>',
						($data['transit_cover_required'] ? 'Y' : 'N'),
						($data['transit_cover_required'] ? $data['transit_cover_amount'] : 0),
						''
					);
					
					$template = file_get_contents($this->getTemplatePath().DS.'article-template.xml');
				}
				$articlesInfo .= str_replace($search, $replace, $template);
			}
		}
		
		return array('info' => $articlesInfo, 'total_weight' => $total_weight);
	}
	
	public function prepareModifiedArticles($order,$consignment_number)
	{
		$articlesInfo = '';
		
		$total_weight = 0;
		
		$articles = Mage::helper('linksynceparcel')->getArticles($order->getId(), $consignment_number);
		foreach($articles as $article)
		{
			$search = array(
				'[[actualWeight]]',
				'[[articleDescription]]',
				'[[height]]',
				'[[length]]',
				'[[width]]',
				'[[isTransitCoverRequired]]',
				'[[transitCoverAmount]]',
				'[[articleNumber]]'
			);
		
			$default_width = 0;
			$use_article_dimensions = (int)Mage::getStoreConfig('carriers/linksynceparcel/use_article_dimensions');
			if($use_article_dimensions == 1)
			{
				$default_width = Mage::getStoreConfig('carriers/linksynceparcel/default_article_width');
			}
			
			$article['weight'] = $this->roundoff_number($article['weight'],2);
			$total_weight += $article['actual_weight'];

			$article['actual_weight'] = $this->calculateWeightDefault($article['actual_weight']);
		
			$replace = array(
				trim($article['actual_weight']),
				trim($article['article_description']),
				$this->zeroIfEmpty($article['height']),
				$this->zeroIfEmpty($article['length']),
				($article['width'] ? '<width>'. $this->zeroIfEmpty($article['width']) .'</width>' : '<width>'.$default_width.'</width>'),
				$article['is_transit_cover_required'],
				(($article['is_transit_cover_required'] == 'Y') ? $article['transit_cover_amount'] : 0),
				'<articleNumber>'.$article['article_number'].'</articleNumber>'
			);
			
			$template = file_get_contents($this->getTemplatePath().DS.'article-template.xml');
			$articlesInfo .= str_replace($search, $replace, $template);
		}
		return array('info' => $articlesInfo, 'total_weight' => $total_weight);
	}

	public function prepareUpdatedArticles($order,$data)
	{
		$articlesInfo = '';
		
		$total_weight = 0;
		
		$articles = Mage::helper('linksynceparcel')->getArticles($order->getId(), $data['consignment_number']);
		foreach($articles as $article)
		{
			$search = array(
				'[[actualWeight]]',
				'[[articleDescription]]',
				'[[height]]',
				'[[length]]',
				'[[width]]',
				'[[isTransitCoverRequired]]',
				'[[transitCoverAmount]]',
				'[[articleNumber]]'
			);
		
			if($article['article_number'] == $data['article_number'])
			{
				$article = $data['article'];
				$article['weight'] = $this->roundoff_number($article['weight'],2);
				$total_weight += $article['weight'];

				$article['weight'] = $this->calculateWeightDefault($article['weight']);

				$replace = array(
					trim($article['weight']),
					$this->xmlData($article['description']),
					$this->zeroIfEmpty($article['height']),
					$this->zeroIfEmpty($article['length']),
					'<width>'. $this->zeroIfEmpty($article['width']) .'</width>',
					($data['transit_cover_required'] ? 'Y' : 'N'),
					($data['transit_cover_required'] ? $data['transit_cover_amount'] : 0),
				''
				);
			}
			else
			{
				$default_width = 0;
				$use_article_dimensions = (int)Mage::getStoreConfig('carriers/linksynceparcel/use_article_dimensions');
				if($use_article_dimensions == 1)
				{
					$default_width = Mage::getStoreConfig('carriers/linksynceparcel/default_article_width');
				}
				
				$article['weight'] = $this->roundoff_number($article['weight'],2);
				$total_weight += $article['actual_weight'];

				$article['actual_weight'] = $this->calculateWeightDefault($article['actual_weight']);

				$replace = array(
					trim($article['actual_weight']),
					trim($article['article_description']),
					$this->zeroIfEmpty($article['height']),
					$this->zeroIfEmpty($article['length']),
					($article['width'] ? '<width>'. $this->zeroIfEmpty($article['width']) .'</width>' : '<width>'.$default_width.'</width>'),
					$article['is_transit_cover_required'],
					($article['transit_cover_amount'] ? $article['transit_cover_amount'] : 0),
					'<articleNumber>'.$article['article_number'].'</articleNumber>'
				);
			}
			
			$template = file_get_contents($this->getTemplatePath().DS.'article-template.xml');
			$articlesInfo .= str_replace($search, $replace, $template);
		}
		return array('info' => $articlesInfo, 'total_weight' => $total_weight);
	}
	
	public function xmlData($text)
	{
		$text = trim($text);
		$text = str_replace('&','&amp;',$text);
		$search = array("<",">",'"',"'");
		$replace = array("&lt;","&gt;","&quot;","&apos;");
		return str_replace($search, $replace, $text);
	}
	
	public function prepareAddArticles($order,$data)
	{
		$articlesInfo = '';
		
		$total_weight = 0;
		
		$articles = Mage::helper('linksynceparcel')->getArticles($order->getId(), $data['consignment_number']);
		foreach($articles as $article)
		{
			$search = array(
				'[[actualWeight]]',
				'[[articleDescription]]',
				'[[height]]',
				'[[length]]',
				'[[width]]',
				'[[isTransitCoverRequired]]',
				'[[transitCoverAmount]]',
				'[[articleNumber]]'
			);
		
			$article['weight'] = $this->roundoff_number($article['weight'],2);
			$total_weight += $article['actual_weight'];

			$article['actual_weight'] = $this->calculateWeightDefault($article['actual_weight']);
			
			$default_width = 0;
			$use_article_dimensions = (int)Mage::getStoreConfig('carriers/linksynceparcel/use_article_dimensions');
			if($use_article_dimensions == 1)
			{
				$default_width = Mage::getStoreConfig('carriers/linksynceparcel/default_article_width');
			}
			
			$replace = array(
				trim($article['actual_weight']),
				trim($article['article_description']),
				$this->zeroIfEmpty($article['height']),
				$this->zeroIfEmpty($article['length']),
				($article['width'] ? '<width>'. $this->zeroIfEmpty($article['width']) .'</width>' : '<width>'.$default_width.'</width>'),
				$article['is_transit_cover_required'],
				( ($article['is_transit_cover_required'] == 'Y') ? $article['transit_cover_amount'] : 0),
				'<articleNumber>'.$article['article_number'].'</articleNumber>'
			);
			
			$template = file_get_contents($this->getTemplatePath().DS.'article-template.xml');
			$articlesInfo .= str_replace($search, $replace, $template);
		}
		
		$search = array(
				'[[actualWeight]]',
				'[[articleDescription]]',
				'[[height]]',
				'[[length]]',
				'[[width]]',
				'[[isTransitCoverRequired]]',
				'[[transitCoverAmount]]',
				'[[articleNumber]]'
		);
	
		if($data['articles_type'] == 'Custom')
		{
			$article = $data['article'];
		}
		else
		{
			$articles_type = $data['articles_type'];
			$articles = explode('<=>',$articles_type);
			
			$article = array();
			$article['description'] = $articles[0];
			$article['weight'] = $articles[1];
			$article['height'] = trim($articles[2]);
			$article['width'] = trim($articles[3]);
			$article['length'] = trim($articles[4]);
			
			$use_order_total_weight = (int)Mage::getStoreConfig('carriers/linksynceparcel/use_order_total_weight');
			if($use_order_total_weight == 1)
			{
				$weight = Mage::helper('linksynceparcel')->getOrderWeight($order);
				$weightPerArticle = Mage::helper('linksynceparcel')->getAllowedWeightPerArticle();
				if($weight == 0)
				{
					$default_article_weight = Mage::getStoreConfig('carriers/linksynceparcel/default_article_weight');
					if($default_article_weight)
					{
						$weight = $default_article_weight;
					}
				}
				if($weight > $weightPerArticle)
				{
					$weight = $weightPerArticle;
				}
				$article['weight'] = $weight;
			}
		}
		
		$article['weight'] = $this->roundoff_number($article['weight'],2);
		$total_weight += $article['weight'];

		$article['weight'] = $this->calculateWeightDefault($article['weight']);
	
		$replace = array(
			trim($article['weight']),
			$this->xmlData($article['description']),
			$this->zeroIfEmpty($article['height']),
			$this->zeroIfEmpty($article['length']),
			'<width>'. $this->zeroIfEmpty($article['width']) .'</width>',
			($data['transit_cover_required'] ? 'Y' : 'N'),
			($data['transit_cover_required'] ? $data['transit_cover_amount'] : 0),
			''
		);
		
		$template = file_get_contents($this->getTemplatePath().DS.'article-template.xml');
		$articlesInfo .= str_replace($search, $replace, $template);
		
		return array('info' => $articlesInfo, 'total_weight' => $total_weight);
	}
	
	public function getOrderCarrier($id_order)
	{
		$allowedChargeCodes = $this->getChargeCodes();
		
		$order = Mage::getModel('sales/order')->load($id_order);
		$shippingCode = $order->getShippingMethod(true)->getCarrierCode(); 

		if($shippingCode != 'linksynceparcel')
		{
			$method = $order->getShippingDescription();
			$charge_code = $this->getNonlinksyncShippingTypeChargecode($method);
			if($charge_code && array_key_exists($charge_code,$allowedChargeCodes))
			{
				$shippingCode = 'linksynceparcel';
			}
			else
			{
				if($charge_code == 'none')
				{
					$shippingCode = 'none';
				}
				else
				{
					if(Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/apply_to_all') && Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/default_chargecode') != '')
					{
						$shippingCode = 'linksynceparcel';
					}
				}
			}
		}
		
		return $shippingCode;
	}
	
	public function isM2eproOrder($id_order)
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('m2epro_order');
		$query = "select id from {$table} where magento_order_id = '{$id_order}'";
		$id = $readConnection->fetchOne($query);
		if($id > 0)
			return true;
		return false;
	}
	
	public function isM2eproOrderMatchLinksyncEparcelTableRates($id_order)
	{
		$order = Mage::getModel('sales/order')->load($id_order);
		
		$description = $order->getM2eproLinksynceparcelShippingDescription();
		$chargecode = $order->getM2eproLinksynceparcelShippingChargecode();
		
		if(! (empty($description) || empty($chargecode)) )
		{
			return array('delivery_type' => $description, 'charge_code' => $chargecode);
		}
		
		$address  = $order->getShippingAddress()->getData();
		$totalWeight = $order->getWeight();
		$storeId = $order->getStoreId();
		
		$websiteId = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
		$countryId = $address['country_id'];
		$regionId = $address['region_id'];
		if($regionId > 0)
		{
			$regionId = Mage::helper('linksynceparcel')->getRegion($regionId);
		}
		else
		{
			$regionId = $address['region'];
		}
		
		$postcode = $address['postcode'];
		
		if($countryId == 'AU')
		{
			$resource = Mage::getSingleton('core/resource');
			$readConnection = $resource->getConnection('core_read');
			$table = $resource->getTableName('linksync_linksynceparcel_tabelrate');
			$query = "select * from {$table} where website_id = '{$websiteId}' AND dest_region_id = '{$regionId}' AND dest_zip = '{$postcode}'";

			$rates = $readConnection->fetchAll($query);
			if($rates && count($rates) > 0 )
			{
				foreach($rates as $rate)
				{
					$writeConnection = $resource->getConnection('core_write');
					$table = $resource->getTableName('sales_flat_order');

					$description = $rate['delivery_type'];
					$chargecode = $rate['charge_code'];
					
					$query = "UPDATE {$table} SET  m2epro_linksynceparcel_shipping_description='{$description}', m2epro_linksynceparcel_shipping_chargecode='{$chargecode}' WHERE entity_id='{$id_order}'";
					$writeConnection->query($query);

					return $rate;
				}
			}
			else
			{
				$query = "select * from {$table} where website_id = '{$websiteId}' AND (dest_region_id='0' OR dest_region_id='' OR dest_region_id  IS NULL OR dest_region_id='*') AND dest_zip = '{$postcode}'";
				$rates = $readConnection->fetchAll($query);
				if($rates && count($rates) > 0 )
				{
					foreach($rates as $rate)
					{
						$writeConnection = $resource->getConnection('core_write');
						$table = $resource->getTableName('sales_flat_order');

						$description = $rate['delivery_type'];
						$chargecode = $rate['charge_code'];
						
						$query = "UPDATE {$table} SET  m2epro_linksynceparcel_shipping_description='{$description}', m2epro_linksynceparcel_shipping_chargecode='{$chargecode}' WHERE entity_id='{$id_order}'";
						$writeConnection->query($query);

						return $rate;
					}
				}
			}
		}
		return false;
	}
	

	public function getOrderChargeCode($id_order,$consignment_number='')
	{
		$order = Mage::getModel('sales/order')->load($id_order);
		$charge_code = $order->getShippingMethod(true)->getMethod(); 
		$allowedChargeCodes = $this->getChargeCodes();
		
		if(!array_key_exists($charge_code,$allowedChargeCodes))
		{
			$method = $order->getShippingDescription();
			$charge_code = $this->getNonlinksyncShippingTypeChargecode($method);
			if(!array_key_exists($charge_code,$allowedChargeCodes))
			{
				if(Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/apply_to_all') && Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/default_chargecode') != '')
				{
					$charge_code = '';
					if(!empty($consignment_number))
					{
						$consignment = $this->getConsignment($order->getId(),$consignment_number);
						$charge_code = $consignment['general_linksynceparcel_shipping_chargecode'];
					}
	
					if(empty($charge_code))
					{
						$charge_code = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/default_chargecode');
					}
				}
			}
		}
		return $charge_code;
	}
	
	public function getChargeCodes()
	{
		$chargeCodes = array(
			'B1' 	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'B2' 	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'B3' 	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
				
			), 
			'B4' 	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'B5' 	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'B96' 	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'B97' 	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'B98' 	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'D1' 	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'DE1' 	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'DE2' 	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'DE4' 	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'DE5' 	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'DE6' 	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'MED1'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'MED2'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'S1'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'S10'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'S2'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'S3'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'S4'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'S5'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'S6'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'S7'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'S8'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'S9'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'SV1'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'SV2'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'W5'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'W6'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'eParcel Standard',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 60,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'X1'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post eParcel',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 61,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			), 
			'X2'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post eParcel',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 61,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			), 
			'X5'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post eParcel',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 61,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			), 
			'X6'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post eParcel',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 61,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'XB1'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post eParcel',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 61,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			), 
			'XB2'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post eParcel',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 61,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'XB3'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post eParcel',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 61,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			), 
			'XB4'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post eParcel',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 61,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			), 
			'XB5'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post eParcel',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 61,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			), 
			'XDE5'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post eParcel',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 61,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'XW5'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post eParcel',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 61,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'XW6'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post eParcel',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 61,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'XS'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post eParcel',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 61,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3E03'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'7E03'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),  
			'3E05'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),  
			'7E05'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'3E33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'7E33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),  
			'3E35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'7E35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'3E53'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'7E53'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'3E55'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),  
			'7E55'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'3E83'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'7E83'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'3E85'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'7E85'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			), 
			'2A33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '500g Parcel Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'2A35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '500g Parcel Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'2B33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '500g Parcel Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'2B35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '500g Parcel Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'2C33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '500g Parcel Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'2C35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '500g Parcel Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'2D33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '500g Parcel Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'2D35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '500g Parcel Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'2G33'	=> array(
				'key'			=> 'express_post',
				'name'			=> '500g Express Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'2G35'	=> array(
				'key'			=> 'express_post',
				'name'			=> '500g Express Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'2H33'	=> array(
				'key'			=> 'express_post',
				'name'			=> '500g Express Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'2H35'	=> array(
				'key'			=> 'express_post',
				'name'			=> '500g Express Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'2I33'	=> array(
				'key'			=> 'express_post',
				'name'			=> '500g Express Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'2I35'	=> array(
				'key'			=> 'express_post',
				'name'			=> '500g Express Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'2J33'	=> array(
				'key'			=> 'express_post',
				'name'			=> '500g Express Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'2J35'	=> array(
				'key'			=> 'express_post',
				'name'			=> '500g Express Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3B03'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'3B05'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'3C03'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'3C05'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'3C33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'3C35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'3C53'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'3C55'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'3C83'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'3C85'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'3D03'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'3D05'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'3D33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'3D35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'3D53'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'3D55'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'3D83'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'3D85'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'3H03'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3H05'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3I03'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3I05'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3I33'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3I35'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3I53'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3I55'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3I83'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3I85'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3J03'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3J05'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3J33'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3J35'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3J53'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3J55'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3J83'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3J85'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3K03'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3K05'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3K33'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3K35'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3K53'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3K55'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3K83'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'3K85'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'4A33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '1kg Parcel Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'4A35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '1kg Parcel Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'4B33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '1kg Parcel Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'4B35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '1kg Parcel Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'4C33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '1kg Parcel Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'4C35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '1kg Parcel Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'4D33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '1kg Parcel Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'4D35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '1kg Parcel Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'4I33'	=> array(
				'key'			=> 'express_post',
				'name'			=> '1kg Express Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'4I35'	=> array(
				'key'			=> 'express_post',
				'name'			=> '1kg Express Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'4J33'	=> array(
				'key'			=> 'express_post',
				'name'			=> '1kg Express Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'4J35'	=> array(
				'key'			=> 'express_post',
				'name'			=> '1kg Express Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7B03'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7B05'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7B33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7B35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7B53'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7B55'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7B83'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7B85'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7C03'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7C05'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7C33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7C35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7C53'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7C55'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7C83'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7C85'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7D03'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7D05'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7D33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7D35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7D53'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7D55'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7D83'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7D85'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7H03'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7H05'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7H33'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7H35'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7H53'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7H55'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7H83'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7H85'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7I03'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7I05'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7I33'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7I35'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7I53'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7I55'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7I83'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7I85'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7J03'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7J05'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7J33'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7J35'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7J53'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7J55'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7J83'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7J85'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7K03'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7K05'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7K33'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7K35'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7K53'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7K55'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7K83'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7K85'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7N33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post Wine',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7N35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post Wine + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7N83'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post Wine',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7N85'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post Wine + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7O33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post Wine',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7O35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post Wine + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7O83'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post Wine',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7O85'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post Wine + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7P33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post Wine',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7P35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post Wine + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7P83'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post Wine',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7P85'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> 'Parcel Post Wine + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'7T33'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post Wine',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7T35'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post Wine + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7T83'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post Wine',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7T85'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post Wine + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7U33'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post Wine',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7U35'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post Wine + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7U83'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post Wine',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7U85'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post Wine + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7V33'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post Wine',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7V35'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post Wine + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7V83'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post Wine',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'7V85'	=> array(
				'key'			=> 'express_post',
				'name'			=> 'Express Post Wine + Signature',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'8A33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '3kg Parcel Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'8A35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '3kg Parcel Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'8B33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '3kg Parcel Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'8B35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '3kg Parcel Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'8C33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '3kg Parcel Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'8C35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '3kg Parcel Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'8D33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '3kg Parcel Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'8D35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '3kg Parcel Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'8G33'	=> array(
				'key'			=> 'express_post',
				'name'			=> '3kg Express Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'8G35'	=> array(
				'key'			=> 'express_post',
				'name'			=> '3kg Express Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'8H33'	=> array(
				'key'			=> 'express_post',
				'name'			=> '3kg Express Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'8H35'	=> array(
				'key'			=> 'express_post',
				'name'			=> '3kg Express Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'8I33'	=> array(
				'key'			=> 'express_post',
				'name'			=> '3kg Express Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'8I35'	=> array(
				'key'			=> 'express_post',
				'name'			=> '3kg Express Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'8J33'	=> array(
				'key'			=> 'express_post',
				'name'			=> '3kg Express Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'8J35'	=> array(
				'key'			=> 'express_post',
				'name'			=> '3kg Express Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'9A33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '5kg Parcel Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'9A35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '5kg Parcel Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'9B33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '5kg Parcel Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'9B35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '5kg Parcel Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'9C33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '5kg Parcel Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'9C35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '5kg Parcel Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'9D33'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '5kg Parcel Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 91,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'9D35'	=> array(
				'key'			=> 'parcel_post',
				'name'			=> '5kg Parcel Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 93,
				'labelType' 	=> 'Parcel Post',
				'template' 		=> 'EPARCEL',
				'serviceType'	=> 'standard',
				'service'		=> 'Std.'
			),
			'9G33' 	=> array(
				'key'			=> 'express_post',
				'name'			=> '5kg Express Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'9G35'	=> array(
				'key'			=> 'express_post',
				'name'			=> '5kg Express Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'9H33'	=> array(
				'key'			=> 'express_post',
				'name'			=> '5kg Express Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'9H35'	=> array(
				'key'			=> 'express_post',
				'name'			=> '5kg Express Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'9I33'	=> array(
				'key'			=> 'express_post',
				'name'			=> '5kg Express Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'9I35'	=> array(
				'key'			=> 'express_post',
				'name'			=> '5kg Express Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'9J33'	=> array(
				'key'			=> 'express_post',
				'name'			=> '5kg Express Post Satchel',
				'serviceCode' 	=> 9,
				'prodCode' 		=> 87,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'9J35'	=> array(
				'key'			=> 'express_post',
				'name'			=> '5kg Express Post Satchel + Sig',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 96,
				'labelType' 	=> 'Express Post',
				'template' 		=> 'EPARCEL EXPRESS',
				'serviceType'	=> 'express',
				'service'		=> 'Exp.'
			),
			'AIR1' 	=> array(
				'key'			=> 'int_economy_air',
				'name'			=> 'Int. Economy Air',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Economy Air',
				'template' 		=> 'ECONOMY AIRMAIL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'AIR2'	=> array(
				'key'			=> 'int_economy_air',
				'name'			=> 'Int. Economy Air',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Economy Air',
				'template' 		=> 'ECONOMY AIRMAIL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'AIR3'	=> array(
				'key'			=> 'int_economy_air',
				'name'			=> 'Int. Economy Air',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Economy Air',
				'template' 		=> 'ECONOMY AIRMAIL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'AIR4'	=> array(
				'key'			=> 'int_economy_air',
				'name'			=> 'Int. Economy Air',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Economy Air',
				'template' 		=> 'ECONOMY AIRMAIL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'AIR5'	=> array(
				'key'			=> 'int_economy_air',
				'name'			=> 'Int. Economy Air',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Economy Air',
				'template' 		=> 'ECONOMY AIRMAIL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'AIR6'	=> array(
				'key'			=> 'int_economy_air',
				'name'			=> 'Int. Economy Air',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Economy Air',
				'template' 		=> 'ECONOMY AIRMAIL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'AIR7'	=> array(
				'key'			=> 'int_economy_air',
				'name'			=> 'Int. Economy Air',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Economy Air',
				'template' 		=> 'ECONOMY AIRMAIL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'AIR8'	=> array(
				'key'			=> 'int_economy_air',
				'name'			=> 'Int. Economy Air',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Economy Air',
				'template' 		=> 'ECONOMY AIRMAIL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'AIR9'	=> array(
				'key'			=> 'int_economy_air',
				'name'			=> 'Int. Economy Air',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Economy Air',
				'template' 		=> 'ECONOMY AIRMAIL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'ECD1'	=> array(
				'key'			=> 'int_express_courier',
				'name'			=> 'Int. Express Courier Document',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Courier Document',
				'template' 		=> 'EXPRESS COURIER INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'ECD2'	=> array(
				'key'			=> 'int_express_courier',
				'name'			=> 'Int. Express Courier Document',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Courier Document',
				'template' 		=> 'EXPRESS COURIER INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'ECD3'	=> array(
				'key'			=> 'int_express_courier',
				'name'			=> 'Int. Express Courier Document',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Courier Document',
				'template' 		=> 'EXPRESS COURIER INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'ECD4'	=> array(
				'key'			=> 'int_express_courier',
				'name'			=> 'Int. Express Courier Document',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Courier Document',
				'template' 		=> 'EXPRESS COURIER INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'ECD5'	=> array(
				'key'			=> 'int_express_courier',
				'name'			=> 'Int. Express Courier Document',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Courier Document',
				'template' 		=> 'EXPRESS COURIER INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'ECD6'	=> array(
				'key'			=> 'int_express_courier',
				'name'			=> 'Int. Express Courier Document',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Courier Document',
				'template' 		=> 'EXPRESS COURIER INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'ECD7'	=> array(
				'key'			=> 'int_express_courier',
				'name'			=> 'Int. Express Courier Document',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Courier Document',
				'template' 		=> 'EXPRESS COURIER INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'ECD8'	=> array(
				'key'			=> 'int_express_courier',
				'name'			=> 'Int. Express Courier Document',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Courier Document',
				'template' 		=> 'EXPRESS COURIER INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'ECD9'	=> array(
				'key'			=> 'int_express_courier',
				'name'			=> 'Int. Express Courier Document',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Courier Document',
				'template' 		=> 'EXPRESS COURIER INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'ECM1'	=> array(
				'key'			=> 'int_express_courier',
				'name'			=> 'Int. Express Courier',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Courier',
				'template' 		=> 'EXPRESS COURIER INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'ECM2'	=> array(
				'key'			=> 'int_express_courier',
				'name'			=> 'Int. Express Courier',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Courier',
				'template' 		=> 'EXPRESS COURIER INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'ECM3'	=> array(
				'key'			=> 'int_express_courier',
				'name'			=> 'Int. Express Courier',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Courier',
				'template' 		=> 'EXPRESS COURIER INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'ECM4'	=> array(
				'key'			=> 'int_express_courier',
				'name'			=> 'Int. Express Courier',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Courier',
				'template' 		=> 'EXPRESS COURIER INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'ECM5'	=> array(
				'key'			=> 'int_express_courier',
				'name'			=> 'Int. Express Courier',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Courier',
				'template' 		=> 'EXPRESS COURIER INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'ECM6'	=> array(
				'key'			=> 'int_express_courier',
				'name'			=> 'Int. Express Courier',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Courier',
				'template' 		=> 'EXPRESS COURIER INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'ECM7'	=> array(
				'key'			=> 'int_express_courier',
				'name'			=> 'Int. Express Courier',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Courier',
				'template' 		=> 'EXPRESS COURIER INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'ECM8'	=> array(
				'key'			=> 'int_express_courier',
				'name'			=> 'Int. Express Courier',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Courier',
				'template' 		=> 'EXPRESS COURIER INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'ECM9'	=> array(
				'key'			=> 'int_express_courier',
				'name'			=> 'Int. Express Courier',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Courier',
				'template' 		=> 'EXPRESS COURIER INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'EPI1'	=> array(
				'key'			=> 'int_express_post',
				'name'			=> 'Int. Express Post',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Post',
				'template' 		=> 'EXPRESS POST INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'EPI2'	=> array(
				'key'			=> 'int_express_post',
				'name'			=> 'Int. Express Post',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Post',
				'template' 		=> 'EXPRESS POST INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'EPI3'	=> array(
				'key'			=> 'int_express_post',
				'name'			=> 'Int. Express Post',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Post',
				'template' 		=> 'EXPRESS POST INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'EPI4'	=> array(
				'key'			=> 'int_express_post',
				'name'			=> 'Int. Express Post',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Post',
				'template' 		=> 'EXPRESS POST INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'EPI5'	=> array(
				'key'			=> 'int_express_post',
				'name'			=> 'Int. Express Post',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Post',
				'template' 		=> 'EXPRESS POST INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'EPI6'	=> array(
				'key'			=> 'int_express_post',
				'name'			=> 'Int. Express Post',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Post',
				'template' 		=> 'EXPRESS POST INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'EPI7'	=> array(
				'key'			=> 'int_express_post',
				'name'			=> 'Int. Express Post',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Post',
				'template' 		=> 'EXPRESS POST INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'EPI8'	=> array(
				'key'			=> 'int_express_post',
				'name'			=> 'Int. Express Post',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Post',
				'template' 		=> 'EXPRESS POST INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'EPI9'	=> array(
				'key'			=> 'int_express_post',
				'name'			=> 'Int. Express Post',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Express Post',
				'template' 		=> 'EXPRESS POST INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'PTI1'	=> array(
				'key'			=> 'int_pack_track',
				'name'			=> 'Int. Pack & Track',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Pack & Track',
				'template' 		=> 'PACK AND TRACK INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'PTI2'	=> array(
				'key'			=> 'int_pack_track',
				'name'			=> 'Int. Pack & Track',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Pack & Track',
				'template' 		=> 'PACK AND TRACK INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'PTI3'	=> array(
				'key'			=> 'int_pack_track',
				'name'			=> 'Int. Pack & Track',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Pack & Track',
				'template' 		=> 'PACK AND TRACK INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'PTI4'	=> array(
				'key'			=> 'int_pack_track',
				'name'			=> 'Int. Pack & Track',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Pack & Track',
				'template' 		=> 'PACK AND TRACK INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'PTI5'	=> array(
				'key'			=> 'int_pack_track',
				'name'			=> 'Int. Pack & Track',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Pack & Track',
				'template' 		=> 'PACK AND TRACK INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'PTI6'	=> array(
				'key'			=> 'int_pack_track',
				'name'			=> 'Int. Pack & Track',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Pack & Track',
				'template' 		=> 'PACK AND TRACK INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'PTI7'	=> array(
				'key'			=> 'int_pack_track',
				'name'			=> 'Int. Pack & Track',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Pack & Track',
				'template' 		=> 'PACK AND TRACK INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'PTI8'	=> array(
				'key'			=> 'int_pack_track',
				'name'			=> 'Int. Pack & Track',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Pack & Track',
				'template' 		=> 'PACK AND TRACK INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'PTI9'	=> array(
				'key'			=> 'int_pack_track',
				'name'			=> 'Int. Pack & Track',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Pack & Track',
				'template' 		=> 'PACK AND TRACK INTERNATIONAL',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'RPI1'	=> array(
				'key'			=> 'int_registered',
				'name'			=> 'Int. Registered',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Registered',
				'template' 		=> 'INTERNATIONAL REGISTERED',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'RPI2'	=> array(
				'key'			=> 'int_registered',
				'name'			=> 'Int. Registered',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Registered',
				'template' 		=> 'INTERNATIONAL REGISTERED',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'RPI3'	=> array(
				'key'			=> 'int_registered',
				'name'			=> 'Int. Registered',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Registered',
				'template' 		=> 'INTERNATIONAL REGISTERED',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'RPI4'	=> array(
				'key'			=> 'int_registered',
				'name'			=> 'Int. Registered',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Registered',
				'template' 		=> 'INTERNATIONAL REGISTERED',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'RPI5'	=> array(
				'key'			=> 'int_registered',
				'name'			=> 'Int. Registered',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Registered',
				'template' 		=> 'INTERNATIONAL REGISTERED',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'RPI6'	=> array(
				'key'			=> 'int_registered',
				'name'			=> 'Int. Registered',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Registered',
				'template' 		=> 'INTERNATIONAL REGISTERED',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'RPI7'	=> array(
				'key'			=> 'int_registered',
				'name'			=> 'Int. Registered',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Registered',
				'template' 		=> 'INTERNATIONAL REGISTERED',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'RPI8'	=> array(
				'key'			=> 'int_registered',
				'name'			=> 'Int. Registered',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Registered',
				'template' 		=> 'INTERNATIONAL REGISTERED',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			),
			'RPI9'	=> array(
				'key'			=> 'int_registered',
				'name'			=> 'Int. Registered',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> 'Int. Registered',
				'template' 		=> 'INTERNATIONAL REGISTERED',
				'serviceType'	=> 'international',
				'service'		=> 'Int.'
			)
		);
		return $chargeCodes;
	}
	
	public function getChargecodeData($chargecode)
	{
		$chargeCodes = $this->getChargeCodes();
		return $chargeCodes[$chargecode]['key'];
	}
	
	public function getChargecodesByService($service)
	{
		$chargeCodes = $this->getChargeCodes();
		$options = array();
		$options[] = array('value' => '', 'label' => 'Please Select Chargecode');
		foreach($chargeCodes as $chargeCode=>$chargeLabel)
		{
			if($service == $chargeLabel['key']) {
				$option = array('value' => $chargeCode, 'label' => $chargeCode);
				$options[] = $option;
			}
		}
		return $options;
	}
	
	public function getServiceOptions()
	{
		$services = array(
			'parcel_post' => 'Parcel Post',
			'express_post' => 'Express Post eParcel',
			'int_economy_air' => 'Int. Economy Air',
			'int_express_courier' => 'Int. Express Courier Document',
			'int_express_post' => 'Int. Express Post',
			'int_pack_track' => 'Int. Pack & Track',
			'int_registered' => 'Int. Registered',
		);
		
		$options = array();
		$options[] = array('value' => '', 'label' => 'Please Select Services');
		foreach($services as $k=>$service) {
			$service_value = Mage::getStoreConfig('carriers/linksynceparcel/'. $k .'_chargecode');
			if(!empty($service_value)) {
				$options[] = array('value' => $service_value, 'label' => $service);
			}
		}
		return $options;
	}
	
	public function updateServiceData($service_type)
	{
		$resource = Mage::getSingleton('core/resource');
	    $writeConnection = $resource->getConnection('core_write');
	    $table = $resource->getTableName('linksync_linksynceparcel_nonlinksync');
		
		$service_value = Mage::getStoreConfig('carriers/linksynceparcel/'. $service_type .'_chargecode');
		
		$query = "UPDATE {$table} SET charge_code='". $service_value ."' WHERE service_type='{$service_type}'";
		$writeConnection->query($query);
	}
	
	public function getChargeCodeOptions($none=false)
	{
		$chargeCodes = $this->getChargeCodes();
		$options = array();
		$option = array('value' => '', 'label' => 'Please Select');
		$options[] = $option;
		if($none)
		{
			$option = array('value' => 'none', 'label' => 'None');
			$options[] = $option;
		}
		foreach($chargeCodes as $chargeCode=>$chargeLabel)
		{
			$option = array('value' => $chargeCode, 'label' => $chargeCode .' - '. $chargeLabel['name']);
			$options[] = $option;
		}
		return $options;
	}
	
	public function getChargeCodeValues($none=false)
	{
		$chargeCodes = $this->getChargeCodes();
		$options = array();
		if($none)
		{
			$options['None'] = array(
				'name'			=> 'None',
				'serviceCode' 	=> 0,
				'prodCode' 		=> 0,
				'labelType' 	=> '',
				'template' 		=> ''
			);
		}
		
		foreach($chargeCodes as $chargeCode => $chargeLabel)
		{
			$options[$chargeCode] = $chargeCode .' - '. $chargeLabel['name'];
		}
		return $options;
	}
	
	public function isCashToCollect($id_order)
	{
		$allowedChargeCodes = array('CS1', 'CS2', 'CS3', 'CS4', 'CS5', 'CS6', 'CS7', 'CS8', 'CX1', 'CX2');
		
		$chargeCode = $this->getOrderChargeCode($id_order);
		if(in_array($chargeCode,$allowedChargeCodes))
		{
			return true;
		}
		return false; 
	}
	
	public function isDisablePartialDeliveryMethod($id_order)
	{
		$allowedChargeCodes = array('PR', 'XPR');
		
		$chargeCode = $this->getOrderChargeCode($id_order);
		if(in_array($chargeCode,$allowedChargeCodes))
		{
			return true;
		}
		return false; 
	}
	
	public function getConsignmentCreateUrl($id_order)
	{
		$url = Mage::getUrl('linksynceparcel/consignment/create/');
		$url .= 'order_id/'.$id_order;
		return $url;
	}
	
	public function insertConsignment($order_id,$consignmentNumber,$data,$manifestNumber,$chargeCode,$total_weight,$country)
	{
		$resource = Mage::getSingleton('core/resource');
	    $writeConnection = $resource->getConnection('core_write');
	    $table = $resource->getTableName('linksync_linksynceparcel_consignment');

		$timestamp = time();
		$date = date('Y-m-d H:i:s', $timestamp);
		
		$query = "INSERT {$table} SET order_id = '{$order_id}', consignment_number='{$consignmentNumber}', add_date='".$date."', delivery_signature_allowed = '".$data['delivery_signature_allowed']."', print_return_labels='".$data['print_return_labels']."', contains_dangerous_goods='".$data['contains_dangerous_goods']."', partial_delivery_allowed = '".$data['partial_delivery_allowed']."', cash_to_collect='".(isset($data['cash_to_collect'])?$data['cash_to_collect']:'')."', email_notification = '".$data['email_notification']."', general_linksynceparcel_shipping_chargecode = '".$chargeCode."', weight = '".$total_weight."', delivery_country = '". $country ."', delivery_instruction = '". addslashes($data['delivery_instruction']) ."', safe_drop = '".$data['safe_drop']."'";
		
		$manifestNumber = trim($manifestNumber);
		if(strtolower($manifestNumber) != 'unassinged')
		{
			$query .= ", manifest_number = '".$manifestNumber."', is_next_manifest = 1";
		}
		$writeConnection->query($query);
		
		if($country != "AU") {
			$this->insertInternationalConsignment($order_id,$consignmentNumber,$data,$country);
		}
	}
	
	public function insertInternationalConsignment($order_id,$consignmentNumber,$data,$country)
	{
		$resource = Mage::getSingleton('core/resource');
	    $writeConnection = $resource->getConnection('core_write');
	    $table = $resource->getTableName('linksync_linksynceparcel_international_fields');
		
		$timestamp = time();
		$date = date('Y-m-d H:i:s', $timestamp);
		
		$product_classification = !empty($data['product_classification'])?$data['product_classification']:991;
		$has_commercial_value = !empty($data['has_commercial_value'])?1:0;
		$country_origin = $data['country_origin'];
		if(!isset($country_origin)) {
			$country_origin = Mage::getStoreConfig('carriers/linksynceparcel/default_country_origin');
		}
		$hs_tariff = $data['hs_tariff'];
		if(!isset($hs_tariff)) {
			$hs_tariff = Mage::getStoreConfig('carriers/linksynceparcel/default_has_tariff');
		}
		
		$insuranceValue = (!empty($data['order_value_insurance']))?$this->getOrderProdItems($data, $order_id, true):$data['insurance_value'];
		
		$query = "INSERT {$table} SET order_id = '{$order_id}', consignment_number='{$consignmentNumber}', add_date='".$date."', insurance = '". $data['insurance'] ."', insurance_value = '". $insuranceValue ."', export_declaration_number='".$data['export_declaration_number']."', has_commercial_value='". $has_commercial_value ."', product_classification = ". $product_classification .", product_classification_text = '".$data['product_classification_text']."', country_origin = '".$country_origin."', hs_tariff = '". $hs_tariff ."', default_contents = '". $data['contents'] ."', ship_country = '". $country ."'";
		
		$writeConnection->query($query);
	}
	
	public function updateConsignment($order_id,$consignmentNumber,$data,$manifestNumber,$chargeCode,$total_weight)
	{
		$resource = Mage::getSingleton('core/resource');
	    $writeConnection = $resource->getConnection('core_write');
	    $table = $resource->getTableName('linksync_linksynceparcel_consignment');

		$timestamp = time();
		$date = date('Y-m-d H:i:s', $timestamp);
		
		$query = "UPDATE {$table} SET delivery_signature_allowed = '".$data['delivery_signature_allowed']."', print_return_labels='".$data['print_return_labels']."', contains_dangerous_goods='".$data['contains_dangerous_goods']."', partial_delivery_allowed = '".$data['partial_delivery_allowed']."', cash_to_collect='".(isset($data['cash_to_collect'])?$data['cash_to_collect']:'')."', email_notification = '".$data['email_notification']."', notify_customers = '".$data['notify_customers']."', general_linksynceparcel_shipping_chargecode = '".$chargeCode."', label = '', is_label_printed=0, is_label_created=0, weight = '".$total_weight."', delivery_country = '". $country ."', delivery_instruction = '". addslashes($data['delivery_instruction']) ."', safe_drop = '".$data['safe_drop']."'";
		
		$manifestNumber = trim($manifestNumber);
		if(strtolower($manifestNumber) != 'unassinged')
		{
			$query .= ", manifest_number = '".$manifestNumber."', is_next_manifest = 1";
		}
		else
		{
			$query .= ", manifest_number = '', is_next_manifest = 0";
		}
		$query .= " WHERE consignment_number='{$consignmentNumber}'"; 
		$writeConnection->query($query);
		
		$filename = $consignmentNumber.'.pdf';
		$filepath = Mage::getBaseDir().DS.'media'.DS.'linksync'.DS.'label'.DS.'consignment'.DS.$filename;
		if(file_exists($filepath))
		{
			unlink($filepath);
		}
		
		$filepath = Mage::getBaseDir().DS.'media'.DS.'linksync'.DS.'label'.DS.'returnlabels'.DS.$filename;
		if(file_exists($filepath))
		{
			unlink($filepath);
		}
	}
	
	public function insertArticles($order_id, $consignmentNumber, $articles, $data)
	{
		$resource = Mage::getSingleton('core/resource');
		$writeConnection = $resource->getConnection('core_write');
	    $table = $resource->getTableName('linksync_linksynceparcel_article');

		try
		{
			$query = '';
			$articleNumbers = $articles->article;
			$number_of_articles = $data['number_of_articles'];
			$start_index = $data['start_index'];
			$end_index = $data['end_index'];
			
			for($i=$start_index, $j=0;$i<=$end_index;$i++,$j++)
			{
				if($data['articles_type'] == 'Custom')
				{
					$article = $data['article'.$i];
				}
				else
				{
					$articles_type = $data['articles_type'];
					$articleTemp = explode('<=>',$articles_type);

					
					$article = array();
					$article['description'] = $articleTemp[0];
					$article['weight'] = $articleTemp[1];
					$article['height'] = trim($articleTemp[2]);
					$article['width'] = trim($articleTemp[3]);
					$article['length'] = trim($articleTemp[4]);
				}
			
				$actualWeight = $article['weight'];
				$articleDescription = $article['description'];
				$articleNumber = (is_array($articleNumbers) ? $articleNumbers[$j] : $articleNumbers);
				$cubicWeight = 0;
				$height = $article['height'];
				$length = $article['length'];
				$width = $article['width'];
				$isTransitCoverRequired = ($data['transit_cover_required'] ? 'Y' : 'N');
				$transitCoverAmount = ($data['transit_cover_required'] ? $data['transit_cover_amount'] : 0);
				
				$query .= "INSERT {$table} SET order_id = '{$order_id}', consignment_number='{$consignmentNumber}',  actual_weight='{$actualWeight}', article_description='".$articleDescription."', article_number='{$articleNumber}', cubic_weight='{$cubicWeight}', height='{$height}', width='{$width}', is_transit_cover_required='{$isTransitCoverRequired}', length='{$length}', transit_cover_amount='{$transitCoverAmount}';";
			}
			$writeConnection->query($query);
		}
		catch(Exception $e)
		{
			throw new Exception($e->getMessage());
		}
	}
	
	public function insertBulkArticles($order_id, $consignmentNumber, $articles, $data)
	{
		$resource = Mage::getSingleton('core/resource');
		$writeConnection = $resource->getConnection('core_write');
	    $table = $resource->getTableName('linksync_linksynceparcel_article');

		try
		{
			$query = '';
			$articleNumbers = $articles->article;
	
			$articles_type = $data['articles_type'];
			$articleTemp = explode('<=>',$articles_type);
			
			$article = array();
			$article['description'] = $articleTemp[0];
			$article['weight'] = $articleTemp[1];
			$article['height'] = trim($articleTemp[2]);
			$article['width'] = trim($articleTemp[3]);
			$article['length'] = trim($articleTemp[4]);
			
			$actualWeight = $article['weight'];
			$articleDescription = $article['description'];
			$articleNumber = (is_array($articleNumbers) ? $articleNumbers[$j] : $articleNumbers);
			$cubicWeight = 0;
			$height = $article['height'];
			$length = $article['length'];
			$width = $article['width'];
			$isTransitCoverRequired = ($data['transit_cover_required'] ? 'Y' : 'N');
			$transitCoverAmount = ($data['transit_cover_required'] ? $data['transit_cover_amount'] : 0);
			
			$query .= "INSERT {$table} SET order_id = '{$order_id}', consignment_number='{$consignmentNumber}',  actual_weight='{$actualWeight}', article_description='".$articleDescription."', article_number='{$articleNumber}', cubic_weight='{$cubicWeight}', height='{$height}', width='{$width}', is_transit_cover_required='{$isTransitCoverRequired}', length='{$length}', transit_cover_amount='{$transitCoverAmount}';";

			$writeConnection->query($query);
		}
		catch(Exception $e)
		{
			throw new Exception($e->getMessage());
		}
	}
	
	public function updateArticles($order_id, $consignmentNumber, $articles, $data,$content)
	{
		$resource = Mage::getSingleton('core/resource');
	    $writeConnection = $resource->getConnection('core_write');
	    $table = $resource->getTableName('linksync_linksynceparcel_article');

		$query = "DELETE FROM {$table} WHERE consignment_number='{$consignmentNumber}'";
		$writeConnection->query($query);
		
		try
		{
			$query = '';
			$articleNumbers = $articles->article;
			$xml = simplexml_load_string($content);
			if($xml)
			{
				$j = 0;
				foreach($xml->articles->article as $article)
				{
					$articleNumber = (is_array($articleNumbers) ? $articleNumbers[$j++] : $articleNumbers);
					$actualWeight = $article->actualWeight;
					$articleDescription = $article->articleDescription;
					$cubicWeight = $article->cubicWeight;
					$height = $article->height;
					$isTransitCoverRequired = $article->isTransitCoverRequired;
					$length = $article->length;
					$width = $article->width;
					$transitCoverAmount = $article->transitCoverAmount;
					
					$query .= "INSERT {$table} SET order_id = '{$order_id}', consignment_number='{$consignmentNumber}',  actual_weight='{$actualWeight}', article_description='". $this->xmlData($articleDescription) ."', article_number='{$articleNumber}', cubic_weight='{$cubicWeight}', height='{$height}', width='{$width}', is_transit_cover_required='{$isTransitCoverRequired}', length='{$length}', transit_cover_amount='{$transitCoverAmount}';";
					
				}
				$writeConnection->query($query);
			}
		}
		catch(Exception $e)
		{
			throw new Exception($e->getMessage());
		}
	}
	
	public function insertArticle($order_id, $consignmentNumber, $articles, $data)
	{
		try
		{
			$articlesExistingNumbers = array();
			$articleNumbers = $articles->article;
			$resource = Mage::getSingleton('core/resource');
			$writeConnection = $resource->getConnection('core_write');
			$table = $resource->getTableName('linksync_linksynceparcel_article');
			
			$articlesData = Mage::helper('linksynceparcel')->getArticles($order_id, $consignmentNumber);
			foreach($articlesData as $article2)
			{
				$articlesExistingNumbers = $article2['article_number'];
			}
			
			$articleNumber = '';
			foreach($articleNumbers as $articleNumberTemp)
			{
				if(!in_array(articleNumberTemp,$articlesExistingNumbers))
				{
					$articleNumber = $articleNumberTemp;
				}
			}
		
			if($data['articles_type'] == 'Custom')
			{
				$article = $data['article'];
			}
			else
			{
				$articles_type = $data['articles_type'];
				$articleTemp = explode('<=>',$articles_type);
						
				$article = array();
				$article['description'] = $articleTemp[0];
				$article['weight'] = $articleTemp[1];
				$article['height'] = trim($articleTemp[2]);
				$article['width'] = trim($articleTemp[3]);
				$article['length'] = trim($articleTemp[4]);
			}
		
			$actualWeight = $article['weight'];
			$articleDescription = $article['description'];
			$cubicWeight = 0;
			$height = $article['height'];
			$length = $article['length'];
			$width = $article['width'];
			$isTransitCoverRequired = ($data['transit_cover_required'] ? 'Y' : 'N');
			$transitCoverAmount = ($data['transit_cover_required'] ? $data['transit_cover_amount'] : 0);
			
			$query .= "INSERT {$table} SET order_id = '{$order_id}', consignment_number='{$consignmentNumber}',  actual_weight='{$actualWeight}', article_description='". $this->xmlData($articleDescription) ."', article_number='{$articleNumber}', cubic_weight='{$cubicWeight}', height='{$height}', width='{$width}', is_transit_cover_required='{$isTransitCoverRequired}', length='{$length}', transit_cover_amount='{$transitCoverAmount}';";
	
			$writeConnection->query($query);
		}
		catch(Exception $e)
		{
			throw new Exception($e->getMessage());
		}
	}
	
	public function updateArticle($articleNumber, $data)
	{
		$resource = Mage::getSingleton('core/resource');
	    $writeConnection = $resource->getConnection('core_write');
	    $table = $resource->getTableName('linksync_linksynceparcel_article');

		try
		{
			$article = $data['article'];
			$actualWeight = $article['weight'];
			$articleDescription = $article['description'];
			$height = $article['height'];
			$isTransitCoverRequired = $data['transit_cover_required'] ? 'Y':'N';
			$length =$article['length'];
			$width = $article['width'];
			$transitCoverAmount = $data['transit_cover_required'] ? $data['transit_cover_amount'] : 0 ;
				
			$query = "UPDATE {$table} SET  actual_weight='{$actualWeight}', article_description='". $articleDescription ."', height='{$height}', width='{$width}', is_transit_cover_required='{$isTransitCoverRequired}', length='{$length}', transit_cover_amount='{$transitCoverAmount}' WHERE article_number='{$articleNumber}'";
			$writeConnection->query($query);
		}
		catch(Exception $e)
		{
			throw new Exception($e->getMessage());
		}
	}
		
	public function getConsignments($id_order,$orderdate=false)
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('linksync_linksynceparcel_consignment');

		$query = "SELECT * FROM {$table} WHERE order_id = '{$id_order}'";
		if($orderdate) {
			$query .= ' ORDER BY `add_date` DESC';
		}
		return $readConnection->fetchAll($query);
	}
	
	public function getInternatioanlConsignments($id_order,$consignment_number)
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('linksync_linksynceparcel_international_fields');

		$query = "SELECT * FROM {$table} WHERE order_id = '{$id_order}' AND consignment_number = '". $consignment_number ."'";
		$records = $readConnection->fetchAll($query);
		return $records[0];
	}
	
	public function getConsignment($id_order,$consignment_number)
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('linksync_linksynceparcel_consignment');

		$query = "SELECT * FROM {$table} WHERE order_id = '{$id_order}' AND consignment_number = '{$consignment_number}'";
		$consignments = $readConnection->fetchAll($query);
		foreach($consignments as $consignment)
		{
			return $consignment;
		}
	}
	
	public function getConsignmentSingle($consignment_number)
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('linksync_linksynceparcel_consignment');

		$query = "SELECT * FROM {$table} WHERE consignment_number = '{$consignment_number}'";
		$consignments = $readConnection->fetchAll($query);
		foreach($consignments as $consignment)
		{
			return $consignment;
		}
	}
	
	public function updateConsignmentSingle($consignment_number, $orderid)
	{
		$resource = Mage::getSingleton('core/resource');
	    $writeConnection = $resource->getConnection('core_write');
	    $table = $resource->getTableName('linksync_linksynceparcel_consignment');
		
		$chargecode = $this->getMatchChargecode($orderid);
		$query = "UPDATE {$table} SET  general_linksynceparcel_shipping_chargecode='{$chargecode}' WHERE consignment_number = '{$consignment_number}'";
		$writeConnection->query($query);
	}
	
	public function getMatchChargecode($orderId) 
	{
		$order = Mage::getModel('sales/order')->load($orderId);
		$address = $order->getShippingAddress();
		$country = $address->getCountry();
		$method = $order->getShippingDescription();
		$charge_code = $this->getNonlinksyncShippingTypeChargecode($method);
		if(Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/apply_to_all'))
		{
			if(!$charge_code && $country == 'AU') {
				$charge_code = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/default_chargecode');
			}
		}
		
		return $charge_code;
	}
	
	public function getArticles($id_order, $consignment_number)
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('linksync_linksynceparcel_article');

		$query = "SELECT * FROM {$table} WHERE order_id = '{$id_order}' AND consignment_number='{$consignment_number}'";
		return $readConnection->fetchAll($query);
	}
	
	public function getArticle($id_order,$consignment_number, $articleNumber)
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('linksync_linksynceparcel_article');

		$query = "SELECT * FROM {$table} WHERE order_id = '{$id_order}' AND consignment_number = '{$consignment_number}' AND article_number='{$articleNumber}'";
		$articles = $readConnection->fetchAll($query);
		foreach($articles as $article)
		{
			return $article;
		}
	}
	
	public function getNotDespatchedConsignmentNumbers()
	{
		$notDespatchedConsignmentsArray = array();
		$notDespatchedConsignments = Mage::getModel('linksynceparcel/api')->getNotDespatchedConsignments();
		if(is_object($notDespatchedConsignments ))
		{
			$notDespatchedConsignmentsArray[] = $notDespatchedConsignments->consignmentNumber;
		}
		else
		{
			foreach($notDespatchedConsignments as $consignment)
			{
				$notDespatchedConsignmentsArray[] = $consignment->consignmentNumber;
			}
		}
		return $notDespatchedConsignmentsArray;
	}
	
	public function getNotDespatchedAssignedConsignmentNumbers()
	{
		$notDespatchedConsignmentsArray = array();
		$notDespatchedConsignments = Mage::getModel('linksynceparcel/api')->getNotDespatchedConsignments();
		if(is_object($notDespatchedConsignments ))
		{
			if($notDespatchedConsignments->status == 'Assigned')
			{
				$notDespatchedConsignmentsArray[] = $notDespatchedConsignments->consignmentNumber;
			}
		}
		else
		{
			foreach($notDespatchedConsignments as $consignment)
			{
				if($consignment->status == 'Assigned')
				{
					$notDespatchedConsignmentsArray[] = $consignment->consignmentNumber;
				}
			}
		}
		return $notDespatchedConsignmentsArray;
	}
	
	public function getNotDespatchedUnassignedConsignmentNumbers()
	{
		$notDespatchedConsignmentsArray = array();
		$notDespatchedConsignments = Mage::getModel('linksynceparcel/api')->getNotDespatchedConsignments();
		if(is_object($notDespatchedConsignments ))
		{
			if($notDespatchedConsignments->status == 'UnAssigned')
			{
				$notDespatchedConsignmentsArray[] = $notDespatchedConsignments->consignmentNumber;
			}
		}
		else
		{
			foreach($notDespatchedConsignments as $consignment)
			{
				if($consignment->status == 'UnAssigned')
				{
					$notDespatchedConsignmentsArray[] = $consignment->consignmentNumber;
				}
			}
		}
		return $notDespatchedConsignmentsArray;
	}
	
	public function getNotDespatchedBothConsignmentNumbers()
	{
		$notDespatchedConsignmentsArray = array();
		$notDespatchedConsignments = Mage::getModel('linksynceparcel/api')->getNotDespatchedConsignments();
		foreach($notDespatchedConsignments as $consignment)
		{
			$notDespatchedConsignmentsArray[$consignment->status][] = $consignment->consignmentNumber;
		}
		return $notDespatchedConsignmentsArray;
	}
	
	public function resubmitConsignment($order_id, $consignment_number)
	{
		$order = Mage::getModel('sales/order')->load($order_id);
		$address = $order->getShippingAddress();
		$country = $address->getCountry();
		if($country == 'AU') {
			$articleData = Mage::helper('linksynceparcel')->prepareModifiedArticle($order, $consignment_number);
			$content = $articleData['content'];
			$charge_code = $articleData['charge_code'];
			try
			{
				return Mage::getModel('linksynceparcel/api')->modifyConsignment($content,$consignment_number,$charge_code);
			}
			catch(Exception $e)
			{
				throw $e;
			}
		}
	}
	
	public function updateConsignmentLabel($order_id,$consignmentNumber,$filename)
	{
		$resource = Mage::getSingleton('core/resource');
	    $writeConnection = $resource->getConnection('core_write');
	    $table = $resource->getTableName('linksync_linksynceparcel_consignment');

		$is_label_created = 0;
		if(!empty($filename))
			$is_label_created = 1;
			
		$query = "UPDATE {$table} SET label = '{$filename}',is_label_created = $is_label_created WHERE order_id = '{$order_id}' AND consignment_number='{$consignmentNumber}'";
		$writeConnection->query($query);
	}
	
	public function removeConsignmentLabels($consignmentNumber)
	{
		$resource = Mage::getSingleton('core/resource');
	    $writeConnection = $resource->getConnection('core_write');
	    $table = $resource->getTableName('linksync_linksynceparcel_consignment');

		$query = "UPDATE {$table} SET label = '', is_label_printed=0, is_label_created=0 WHERE consignment_number='{$consignmentNumber}'";
		$writeConnection->query($query);
		
		$filename = $consignmentNumber.'.pdf';
		$filepath = Mage::getBaseDir().DS.'media'.DS.'linksync'.DS.'label'.DS.'consignment'.DS.$filename;
		if(file_exists($filepath))
		{
			unlink($filepath);
		}
		
		$filename = $consignmentNumber.'.pdf';
		$filepath = Mage::getBaseDir().DS.'media'.DS.'linksync'.DS.'label'.DS.'returnlabels'.DS.$filename;
		if(file_exists($filepath))
		{
			unlink($filepath);
		}
	}
	
	public function updateConsignmentTable($order_id,$consignmentNumber,$columnName, $value)
	{
		$resource = Mage::getSingleton('core/resource');
	    $writeConnection = $resource->getConnection('core_write');
	    $table = $resource->getTableName('linksync_linksynceparcel_consignment');

		$query = "UPDATE {$table} SET {$columnName} = '{$value}' WHERE order_id = '{$order_id}' AND consignment_number='{$consignmentNumber}'";
		$writeConnection->query($query);
	}
	
	public function updateConsignmentTable2($consignmentNumber,$columnName, $value)
	{
		$resource = Mage::getSingleton('core/resource');
	    $writeConnection = $resource->getConnection('core_write');
	    $table = $resource->getTableName('linksync_linksynceparcel_consignment');

		$query = "UPDATE {$table} SET {$columnName} = '{$value}' WHERE consignment_number='{$consignmentNumber}'";
		$writeConnection->query($query);
	}
	
	public function updateConsignmentTableByManifest($manifestNumber,$columnName, $value)
	{
		$resource = Mage::getSingleton('core/resource');
	    $writeConnection = $resource->getConnection('core_write');
	    $table = $resource->getTableName('linksync_linksynceparcel_consignment');

		$query = "UPDATE {$table} SET {$columnName} = '{$value}' WHERE manifest_number='{$manifestNumber}'";
		$writeConnection->query($query);
	}
	
	public function getOrdersByManifest($manifestNumber)
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('linksync_linksynceparcel_consignment');

		$query = "SELECT order_id FROM {$table} WHERE manifest_number = '{$manifestNumber}' group by order_id";
		return $readConnection->fetchAll($query);
	}
	
	public function deleteConsignment($order_id,$consignmentNumber)
	{
		$resource = Mage::getSingleton('core/resource');
	    $writeConnection = $resource->getConnection('core_write');
	    $table = $resource->getTableName('linksync_linksynceparcel_consignment');

		$query = "DELETE FROM {$table} WHERE order_id = '{$order_id}' AND consignment_number='{$consignmentNumber}'";
		$writeConnection->query($query);
		
	    $table = $resource->getTableName('linksync_linksynceparcel_article');

		$query = "DELETE FROM {$table} WHERE order_id = '{$order_id}' AND consignment_number='{$consignmentNumber}'";
		$writeConnection->query($query);		
	}
	
	public function deleteArticle($order_id,$consignmentNumber, $articleNumber)
	{
		$resource = Mage::getSingleton('core/resource');
	    $writeConnection = $resource->getConnection('core_write');
	    $table = $resource->getTableName('linksync_linksynceparcel_article');

		$deleteToBeArticle = $this->getArticle($order_id, $consignmentNumber, $articleNumber);
		
		$query = "DELETE FROM {$table} WHERE order_id = '{$order_id}' AND consignment_number='{$consignmentNumber}' AND article_number='{$articleNumber}'";
		$writeConnection->query($query);
		return $deleteToBeArticle;
	}
	
	public function getDeliveryTypeOptions()
	{
		$collection = Mage::getModel('sales/order')->getCollection();
		$collection->addAttributeToFilter('shipping_method', array('like' => '%linksynceparcel%'));
		$options = array();
		$title = Mage::getStoreConfig('carriers/linksynceparcel/title');
		foreach($collection as $order)
		{
			$method =  $order->getShippingMethod();
			$method = explode('_',$method);
			$options[$method[1]] = $method[1];
		}
		
		$collection = Mage::getModel('linksynceparcel/consignment')->getCollection();
		$collection
			->getSelect()
			->where('general_linksynceparcel_shipping_chargecode !="" ')
			->group('main_table.general_linksynceparcel_shipping_chargecode');

		foreach($collection as $consignment)
		{
			$chargecode =  $consignment->getGeneralLinksynceparcelShippingChargecode();
			if($chargecode)
			{
				if(!in_array($chargecode,$options))
				{
					$options[$chargecode] = $chargecode;
				}
			}
		}
		
		if(Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/apply_to_all'))
		{
			$chargecode = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/default_chargecode');
			if(!in_array($chargecode,$options))
			{
				$options[$chargecode] = $chargecode;
			}
		}
		return $options;
	}
	
	public function getDeliveryTypeOptions2()
	{
		$options = array();
		$title = Mage::getStoreConfig('carriers/linksynceparcel/title');

		$collection = Mage::getModel('linksynceparcel/consignment')->getCollection();
		$collection
			->getSelect()
			->where('general_linksynceparcel_shipping_chargecode !="" ')
			->group('main_table.general_linksynceparcel_shipping_chargecode');

		foreach($collection as $consignment)
		{
			$chargecode =  $consignment->getGeneralLinksynceparcelShippingChargecode();
			if($chargecode)
			{
				if(!in_array($chargecode,$options))
				{
					$options[$chargecode] = $chargecode;
				}
			}
		}
		
		if(Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/apply_to_all'))
		{
			$chargecode = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/default_chargecode');
			if(!in_array($chargecode,$options))
			{
				$options[$chargecode] = $chargecode;
			}
		}
		return $options;
	}
	
	public function getConsignmentLabelUrl()
	{
		$storeUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		$storeUrl .= 'media/linksync/label/consignment/';
		return $storeUrl;
	}
	
	public function getConsignmentReturnLabelUrl()
	{
		$storeUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		$storeUrl .= 'media/linksync/label/returnlabels/';
		return $storeUrl;
	}
	
	public function isReturnLabelFileExists($consignmentNumber)
	{
		$filename = $consignmentNumber.'.pdf';
		$filepath = Mage::getBaseDir().DS.'media'.DS.'linksync'.DS.'label'.DS.'returnlabels'.DS.$filename;
		return file_exists($filepath);
	}
	
	public function getManifestLabelUrl()
	{
		$storeUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		$storeUrl .= 'media/linksync/label/manifest/';
		return $storeUrl;
	}
	
	public function getManifest($manifest_number)
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('linksync_linksynceparcel_manifest');

		$query = "SELECT * FROM {$table} WHERE manifest_number = '{$manifest_number}'";
		$manifests = $readConnection->fetchAll($query);
		foreach($manifests as $manifest)
		{
			return $manifest;
		}
		return false;
	}
	
	public function deleteManifest()
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('linksync_linksynceparcel_consignment');

		$query = "SELECT * FROM {$table} WHERE despatched = 0 and is_next_manifest = 1";
		$results = $readConnection->fetchAll($query);
		if(count($results) == 0)
		{
			$resource = Mage::getSingleton('core/resource');
			$writeConnection = $resource->getConnection('core_write');
			$table = $resource->getTableName('linksync_linksynceparcel_manifest');
			$query = "DELETE FROM {$table} WHERE despatch_date is null or despatch_date = '' order by manifest_number desc limit 1";
			$writeConnection->query($query);
		}
		Mage::getModel('core/config')->saveConfig('carriers/linksynceparcel/manifest_sync', 1);
	}
	
	public function deleteManifest2($manifestNumber)
	{
		if(!empty($manifestNumber))
		{
			$resource = Mage::getSingleton('core/resource');
			$readConnection = $resource->getConnection('core_read');
			$table = $resource->getTableName('linksync_linksynceparcel_consignment');
	
			$query = "SELECT * FROM {$table} WHERE manifest_number = '$manifestNumber'";
			$results = $readConnection->fetchAll($query);
			if(count($results) == 0)
			{
				$writeConnection = $resource->getConnection('core_write');
				$table = $resource->getTableName('linksync_linksynceparcel_manifest');
				$query = "DELETE FROM {$table} WHERE manifest_number = '$manifestNumber'";
				$writeConnection->query($query);
			}
		}
	}
	
	public function insertManifest($manifestNumber,$numberOfArticles=0,$numberOfConsignments=0)
	{
		$resource = Mage::getSingleton('core/resource');
	    $writeConnection = $resource->getConnection('core_write');
	    $table = $resource->getTableName('linksync_linksynceparcel_manifest');
		
		if(!$this->getManifest($manifestNumber))
		{
			$manifestNumber = trim($manifestNumber);
			if(strtolower($manifestNumber) != 'unassinged')
			{
				$query = "INSERT {$table} SET manifest_number='{$manifestNumber}', number_of_articles = '{$numberOfArticles}', number_of_consignments='{$numberOfConsignments}'";
				$writeConnection->query($query);
			}
		}
		Mage::getModel('core/config')->saveConfig('carriers/linksynceparcel/manifest_sync', 1);
	}
	
	public function updateManifest($manifestNumber,$numberOfArticles,$numberOfConsignments)
	{
		$resource = Mage::getSingleton('core/resource');
	    $writeConnection = $resource->getConnection('core_write');
	    $table = $resource->getTableName('linksync_linksynceparcel_manifest');
		
		if($this->getManifest($manifestNumber))
		{
			$query = "UPDATE {$table} SET number_of_articles = '{$numberOfArticles}', number_of_consignments='{$numberOfConsignments}' WHERE manifest_number='{$manifestNumber}'";
			$writeConnection->query($query);
		}
		else
		{
			$query = "INSERT {$table} SET number_of_articles = '{$numberOfArticles}', manifest_number='{$manifestNumber}', number_of_consignments='{$numberOfConsignments}'";
			$writeConnection->query($query);
		}
	}
	
	public function updateManifestTable($manifestNumber,$columnName, $value)
	{
		$resource = Mage::getSingleton('core/resource');
	    $writeConnection = $resource->getConnection('core_write');
	    $table = $resource->getTableName('linksync_linksynceparcel_manifest');

		$query = "UPDATE {$table} SET {$columnName} = '{$value}' WHERE manifest_number='{$manifestNumber}'";
		$writeConnection->query($query);
	}
	
	public function getManifestNumber()
	{
		try
		{
			$manifestNumber = false;
			$manifests = Mage::getModel('linksynceparcel/api')->getManifest();
			$xml = simplexml_load_string($manifests);
			Mage::log('manifest xml: '.preg_replace('/\s+/', ' ', trim($manifests)), null, 'linksync_eparcel.log', true);
			$currentManifest = '';
			if($xml)
			{
				foreach($xml->manifest as $manifest)
				{
					$manifestNumber = $manifest->manifestNumber;
					if(empty($currentManifest))
					{
						$currentManifest = $manifestNumber;
					}
					
					$numberOfArticles = (int)$manifest->numberOfArticles;
					$numberOfConsignments = (int)$manifest->numberOfConsignments;
					Mage::helper('linksynceparcel')->updateManifest($manifestNumber,$numberOfArticles,$numberOfConsignments);
				}
				
				Mage::getModel('core/config')->saveConfig('carriers/linksynceparcel/manifest_sync', 0);
			}
			return $currentManifest;
		}
		catch(Exception $e)
		{
			Mage::log('getManifestNumber: '.$e->getMessage(), null, 'linksync_eparcel.log', true);
			return false;
		}
	}
	
	public function getConsignmentsByNumber($manifestNumber)
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('linksync_linksynceparcel_consignment');

		$query = "SELECT * FROM {$table} WHERE manifest_number = '{$manifestNumber}'";
		return $readConnection->fetchAll($query);
	}
	
	public function notifyCustomers($manifestNumber)
	{
		$consignments = $this->getConsignmentsByNumber($manifestNumber);
		foreach($consignments as $consignment)
		{
			$order = Mage::getModel('sales/order')->load($consignment['order_id']);
			$toEmail = $order->getCustomerEmail();
			$toName = $order->getCustomerFirstname().' '.$order->getCustomerLastname();
			$fromEmail  = $this->getStoreConfig('carriers/linksynceparcel/from_email_address');
			$fromName = $this->getStoreConfig('general/store_information/name');
			$subject  = $this->getStoreConfig('carriers/linksynceparcel/subject');
			$siteUrl = Mage::app()->getStore()->getHomeUrl();

			$content  = $this->getStoreConfig('carriers/linksynceparcel/email_body');
			$content = str_replace('[TrackingNumber]',$consignment['consignment_number'],$content);
			
			$search = array(
				'[TrackingNumber]',
				'[OrderNumber]',
				'[CustomerFirstname]'
			);
	
			$replace = array(
				$consignment['consignment_number'],
				$this->getIncrementId($order),
				$order->getCustomerFirstname()
			);
			
			$subject = str_replace($search, $replace, $subject);
			$content = str_replace($search, $replace, $content);
		
			/*$mail = Mage::getModel('core/email');
			$mail->setToName($toName);
			$mail->setToEmail($toEmail);
			$mail->setBody($content);
			$mail->setSubject($subject);
			if(!empty($fromEmail))
			{
				$mail->setFromEmail($fromEmail);
			}
			if(!empty($fromName))
			{
				$mail->setFromName($fromName);
			}
			$mail->setType('html');
			$mail->send();*/
			
			$emailTemplate  = Mage::getModel('core/email_template')
					->loadDefault('notify_customer_on_despatch');
					
			$emailTemplateVariables = array();
			$emailTemplateVariables['content'] = $content;
			
			$emailTemplate->setType('html');
			if(!empty($fromEmail))
			{
				$emailTemplate->setSenderEmail($fromEmail);
			}
			if(!empty($fromName))
			{
				$emailTemplate->setSenderName($fromName);
			}
			$emailTemplate->setTemplateSubject($subject);

			$emailTemplate->send($toEmail,$toName, $emailTemplateVariables);
		}
		
	}
	
	public function getFreeshipping($charge_code,$price)
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('linksync_linksynceparcel_free_shipping');
		$charge_code = trim($charge_code);

		$query = "select * from {$table} where charge_code = '{$charge_code}' and status = 1 order by from_amount";
		$result = $readConnection->fetchAll($query);
		if($result && count($result) > 0)
		{
			foreach($result as $row)
			{
				if($price >= $row['from_amount'])
                {
					if($row['to_amount'] > 0 )
					{
						if($price <= $row['to_amount'])
						{
							return $row;
						}
					}
					else
					{
						return $row;
					}
				}
			}
		}
		return false;
	}
	
	public function getRegion($region_id)
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('directory_country_region');

		$query = "select code from {$table} where region_id = '{$region_id}'";
		return $readConnection->fetchOne($query);
	}
	
	public function labelCreate($consignmentNumber)
    {
		try
		{
			$labelContent = Mage::getModel('linksynceparcel/api')->getLabelsByConsignments($consignmentNumber);

			if($labelContent)
			{
				$filename = $consignmentNumber.'.pdf';
				$filepath = Mage::getBaseDir().DS.'media'.DS.'linksync'.DS.'label'.DS.'consignment'.DS.$filename;
				$handle = fopen($filepath,'wb');
				fwrite($handle, $labelContent);
				fclose($handle);
				Mage::helper('linksynceparcel')->updateConsignmentTable2($consignmentNumber,'label',$filename);
				Mage::helper('linksynceparcel')->updateConsignmentTable2($consignmentNumber,'is_label_created',1);
			}
		}
		catch(Exception $e)
		{
			;//log
		}
	}
	
	public function returnLabelCreate($consignmentNumber)
    {
		try
		{
			$consignment = $this->getConsignmentSingle($consignmentNumber);
			if($consignment['print_return_labels'])
			{
				$labelContent = Mage::getModel('linksynceparcel/api')->getReturnLabelsByConsignments($consignmentNumber);
	
				if($labelContent)
				{
					$filename = $consignmentNumber.'.pdf';
					$filepath = Mage::getBaseDir().DS.'media'.DS.'linksync'.DS.'label'.DS.'returnlabels'.DS.$filename;
					$handle = fopen($filepath,'wb');
					fwrite($handle, $labelContent);
					fclose($handle);
					Mage::helper('linksynceparcel')->updateConsignmentTable2($consignmentNumber,'is_return_label_printed',0);
				}
			}
		}
		catch(Exception $e)
		{
			;//log
		}
	}
	
	function createZip($file,$archiveFile) 
	{
		$ziph = new ZipArchive();
		if(file_exists($archiveFile))
		{
			if($ziph->open($archiveFile, ZIPARCHIVE::CHECKCONS) !== TRUE)
			{
				throw new Exception("Unable to Open $archiveFile");
			}
		}
		else
		{
		  	if($ziph->open($archiveFile, ZIPARCHIVE::CM_PKWARE_IMPLODE) !== TRUE)
		  	{
				throw new Exception("Could not Create $archiveFile");
		  	}
		}
		
		if(file_exists($file))
		{
			if(is_readable($file))
			{
			  	if(!$ziph->addFile($file,'linksync_eparcel.log'))
				{
				  throw new Exception("Error archiving $file in $archiveFile");
				}
			}
			else
			{
				throw new Exception("Error archiving $file is not readable");
			}
		}
		else
		{
			throw new Exception("Error archiving $file is not exist");
		}		
		
		$ziph->close();
		
		return true;
	}
	
	public function getQuote()
	{
		return Mage::getSingleton('checkout/session')->getQuote();
	}
	
	public function getAdminQuote()
	{
		return Mage::getSingleton('adminhtml/session_quote')->getQuote();
	}
	
	public function isCurrentMainfestHasConsignmentsForDespatch()
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('linksync_linksynceparcel_consignment');

		$query = "SELECT order_id FROM {$table} WHERE despatched=0 and is_next_manifest=1";
		$result = $readConnection->fetchAll($query);
		if(count($result) > 0)
			return true;
		return false;
	}
	
	public function isCurrentMainfestHasConsignmentsForReturnLabel()
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('linksync_linksynceparcel_consignment');

		$query = "SELECT order_id FROM {$table} WHERE despatched=0 and is_next_manifest=1 and print_return_labels=1";
		$result = $readConnection->fetchAll($query);
		if(count($result) > 0)
			return true;
		return false;
	}
	
	public function getShippingAdress($order_id)
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('sales_flat_order_address');
		$table2 = $resource->getTableName('sales_flat_order');
		
		$query = "SELECT order_address.* FROM {$table} as order_address LEFT JOIN {$table2} as order_table ON order_table.shipping_address_id = order_address.entity_id WHERE order_table.entity_id = '{$order_id}'";

		$addresses = $readConnection->fetchAll($query);
		foreach($addresses as $address)
		{
			return $address;
		}
		return false;
	}
	
	public function getExpressPostCodes()
	{
		$codes1 = array('X1','X2','X5','X6','XB1','XB2','XB3','XB4','XB5','XDE5','XW5','XW6','XS','7J55');
		$codes2 = array('2G33','2G35','2H33','2H35','2I33','2I35','2J33','2J35','3H03','3H05','3I03','3I05','3I33','3I35','3I53','3I55','3I83','3I85','3J03','3J05','3J33','3J35','3J53','3J55','3J83','3J85','3K03','3K05','3K33','3K35','3K53','3K55','3K83','3K85','4I33','4I35','4J33','4J35','7H03','7H05','7H33','7H35','7H53','7H55','7H83','7H85','7I03','7I05','7I33','7I35','7I53','7I55','7I83','7I85','7J03','7J05','7J33','7J35','7J53','7J55','7J83','7J85','7K03','7K05','7K33','7K35','7K53','7K55','7K83','7K85','7T33','7T35','7T83','7T85','7U33','7U35','7U83','7U85','7V33','7V35','7V83','7V85','8G33','8G35','8H33','8H35','8I33','8I35','8J33','8J35','9G33','9G35','9H33','9H35','9I33','9I35','9J33','9J35');
		return array_merge($codes1,$codes2);
	}
	
	public function getLinksynceparcelStandardCodes()
	{
		$codes1 = array('B1','B2','B3','B4','B5','B96','B97','B98','D1','DE1','DE2','DE4','DE5','DE6','MED1','MED2','S1','S10','S2','S3','S4','S5','S6','S7','S8','S9','SV1','SV2','W5','W6','7D55');
		$codes2 = array('3E03','7E03','3E05','7E05','3E33','7E33','3E35','7E35','3E53','7E53','3E55','7E55','3E83','7E83','3E85','7E85','2A33','2A35','2B33','2B35','2C33','2C35','2D33','2D35','3B03','3B05','3C03','3C05','3C33','3C35','3C53','3C55','3C83','3C85','3D03','3D05','3D33','3D35','3D53','3D55','3D83','3D85','4A33','4A35','4B33','4B35','4C33','4C35','4D33','4D35','7B03','7B05','7B33','7B35','7B53','7B55','7B83','7B85','7C03','7C05','7C33','7C35','7C53','7C55','7C83','7C85','7D03','7D05','7D33','7D35','7D53','7D55','7D83','7D85','7N33','7N35','7N83','7N85','7O33','7O35','7O83','7O85','7P33','7P35','7P83','7P85','8A33','8A35','8B33','8B35','8C33','8C35','8D33','8D35','9A33','9A35','9B33','9B35','9C33','9C35','9D33','9D35');
		return array_merge($codes1,$codes2);
	}
	
	public function isExpressPostCode($code)
	{
		$codes = $this->getExpressPostCodes();
		return in_array($code,$codes);
	}
	
	public function isLinksynceparcelStandardCode($code)
	{
		$codes = $this->getLinksynceparcelStandardCodes();
		return in_array($code,$codes);
	}
	
	public function isAddressValid($id)
	{
		$order = Mage::getModel('sales/order')->load($id);
		return $order->getIsAddressValid();
	}
	
	public function getNonlinksyncShippingTypes()
	{
		$collection = Mage::getModel('sales/order')->getCollection();
		$collection->addAttributeToFilter('shipping_method', array('nlike' => '%linksynceparcel%'));
		$collection
			->getSelect()
			->order('main_table.shipping_description asc')
			->group('main_table.shipping_description');
			
		$options = array();
		foreach($collection as $order)
		{
			$options[$order->getShippingDescription()] = $order->getShippingDescription();
		}
		return $options;
	}
	
	public function getNonlinksyncShippingTypeOptions()
	{
		$getNonlinksyncShippingTypes = $this->getNonlinksyncShippingTypes();
		$options = array();
		$option = array('value' => '', 'label' => 'Please Select');
		$options[] = $option;

		foreach($getNonlinksyncShippingTypes as $key => $val)
		{
			$option = array('value' => $key, 'label' => $val);
			$options[] = $option;
		}
		return $options;
	}
	
	public function getNonlinksyncShippingTypeChargecode($method)
	{
		$method = addslashes($method);
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('linksync_linksynceparcel_nonlinksync');
		$query = "SELECT charge_code FROM {$table} WHERE method = '". $method ."'";
		return $readConnection->fetchOne($query);
	}
	
	public function getChargecodeMethodType($method)
	{
		$chargecode = $this->getNonlinksyncShippingTypeChargecode($method);
		$chargeCodes = Mage::helper('linksynceparcel')->getChargeCodes();
		$chargeCodeData = $chargeCodes[$chargecode];
		$type = '';
		switch($chargeCodeData['serviceType']) {
			case 'express':
				$type = 'domestic';
				break;
			case 'standard':
				$type = 'domestic';
				break;
			case 'international':
				$type = 'international';
				break;
		}
		return $type;
	}
	
	public function getChargecodeType($orderid)
	{
		$chargecode = $this->getOrderChargeCode($orderid);
		$chargeCodes = Mage::helper('linksynceparcel')->getChargeCodes();
		$chargeCodeData = $chargeCodes[$chargecode];
		$type = '';
		switch($chargeCodeData['serviceType']) {
			case 'express':
				$type = 'domestic';
				break;
			case 'standard':
				$type = 'domestic';
				break;
			case 'international':
				$type = 'international';
				break;
		}
		return $type;
	}
	
	public function getOrderShippingTypes()
	{
		$collection = Mage::getModel('sales/order')->getCollection();
		$collection->addAttributeToFilter('shipping_method', array('nlike' => '%linksynceparcel%'));
		$collection
			->getSelect()
			->order('main_table.shipping_description asc')
			->group('main_table.shipping_description');
			
		$options = array();
		foreach($collection as $order)
		{
			$method = $order->getShippingMethod();
			$description = $order->getShippingDescription();		
			$chargecodeType = $this->getChargecodeMethodType($description);
			$options[base64_encode($method.'###'.$description)] = array(
				'type' => $chargecodeType,
				'description' => $description,
			);
		}
		return $options;
	}
	
	public function getEparcelShippingOptions($currentMethod,$changeDescription,$orderid)
	{
		/*$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('linksync_linksynceparcel_tabelrate');*/

		$options = array();
		$chargecodeType = $this->getChargecodeType($orderid);
		$types = $this->getOrderShippingTypes();
		foreach($types as $code => $val)
		{
			$order_method = base64_encode($currentMethod.'###'.$changeDescription);
			if($code != $order_method && $val['type'] == $chargecodeType)
			{
				$options[$code] = $val['description'];
			}
		}
		return $options;
		/*$query = "SELECT * FROM {$table} group by delivery_type,charge_code";
		$rows = $readConnection->fetchAll($query);
		foreach($rows as $row)
		{
			$description = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/title').' - '.$row['delivery_type'];
			$method = 'linksynceparcel_'.$row['charge_code'];
			$options[base64_encode($method.'###'.$description)] = $description.' ('.$row['charge_code'].')';
		}
		
		$methods = $this->getActiveShippingMethods();
		foreach($methods as $method)
		{
			$title = $method['label'];
			$submethods = $method['value'];
			foreach($submethods as $submethod)
			{
				$code = $submethod['value'];
				if($code != $currentMethod)
				{
					$label = trim($submethod['label']);
					if(empty($label))
						$description = $title;
					else
						$description = $title.' - '.$submethod['label'];
						
					$options[base64_encode($code.'###'.$description)] = $description;
				}
			}
		}*/
		
		return $options;
	}
	
	public function getActiveShippingMethods()
    {
        $methods = array();

        $activeCarriers = Mage::getSingleton('shipping/config')->getActiveCarriers();
        foreach($activeCarriers as $carrierCode => $carrierModel)
        {
			if($carrierCode != 'linksynceparcel')
			{
				$options = array();
				if( $carrierMethods = $carrierModel->getAllowedMethods() )
				{
				   foreach ($carrierMethods as $methodCode => $method)
				   {
						$methodCode = trim($methodCode);
						if(empty($methodCode))
							$code= $carrierCode;
						else
							$code= $carrierCode.'_'.$methodCode;
							
						$options[]=array('value'=>$code,'label'=>$method);
				   }
				   $carrierTitle = Mage::getStoreConfig('carriers/'.$carrierCode.'/title');
				
				}
				$methods[]=array('value'=>$options,'label'=>$carrierTitle);
			}
        }
        return $methods;
    }
	
	public function getExtensionVersion() 
	{ 
		return (string) Mage::getConfig()->getNode()->modules->Linksync_Linksynceparcel->version; 
	}
	
	public function getOrderWeight($order)
	{
		$weight = $order->getWeight();
		$product_unit = trim(Mage::getStoreConfig('carriers/linksynceparcel/product_unit'));
		$packaging_allowance_type = trim(Mage::getStoreConfig('carriers/linksynceparcel/packaging_allowance_type'));
		if($weight > 0 && $product_unit == 'grams')
		{
			$weight = $weight / 1000;
			$weight = number_format($weight,2,'.', '');
		}
		
		$packaging_allowance_value = trim(Mage::getStoreConfig('carriers/linksynceparcel/packaging_allowance_value'));
		if($packaging_allowance_value > 0)
		{
			if($packaging_allowance_type == 'F')
			{
				$weight += $packaging_allowance_value;
			}
			else
			{
				$weight += ($weight * ($packaging_allowance_value/100));
			}
		}
		return $weight;
	}
	
	public function getAllowedWeightPerArticle()
	{
		return 22;
	}
	
	public function presetMatch($presets,$weight)
	{
		$selected = false;
		if($presets && count($presets) > 0)
		{
			foreach($presets as $preset)
			{
				$presetWeight = floatval($preset->getWeight());
				$weight = floatval($weight);
				
				$presetWeight = ''.$presetWeight.'';
				$weight = ''.$weight.'';
		
				if($presetWeight == $weight)
				{
					$selected = true;
					break;
				}
			}
		}
		return $selected;
	}
	
	public function getSiteUrl($api=false)
	{
		$url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		if($api) {
			$parseurl = parse_url($url);
			$url = str_replace('www.', '', str_replace($parseurl['scheme'].'://', '', $url));
			$url = str_replace('/', '', $url);
		}
		return $url;
	}
	
	public function getOrderWeightTotal($orderid) 
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('linksync_linksynceparcel_article');

		$query = "SELECT actual_weight FROM {$table} WHERE order_id = '{$orderid}'";
		$results = $readConnection->fetchAll($query);
		$total = 0;
		if($results && count($results) > 0) {
			foreach($results as $result) {
				$total += $result['actual_weight'];
			}
		}
		return $total;
	}
	
	public function validateInternationalConsignment($data, $order, $country='AU')
	{
		$errors = array();
		if($data['articles_type'] == 'order_weight') {
			$weight = Mage::helper('linksynceparcel')->getOrderWeight($order);
			if($weight == 0)
			{
				$default_article_weight = Mage::getStoreConfig('carriers/linksynceparcel/default_article_weight');
				if($default_article_weight)
				{
					$weight = $default_article_weight;
				}
			}
			$description = "Article 1";
		} else {
			if($data['articles_type'] == 'Custom' && !empty($data['article1'])) {
				$description = $data['article1']['description'];
				$weight = $data['article1']['weight'];
			} else {
				$articles_type = $data['articles_type'];
				$articles = explode('<=>',$articles_type);
				$description = $articles[0];
				$weight = $articles[1];
			}
		}
		
		// Check if admin user wan't to overide order weight
		if($data['edit_order_weight'] == 1) {
			$weight = $data['default_order_weight'];
		}
		
		if(empty($description))
			$errors[] = '<strong>Article Description</strong> is a required field.';
		if(empty($weight))
			$errors[] = '<strong>Weight (Kgs)</strong> is a required field.';
		
		$chargeCode = $this->getChargeCode($order);
		$allowedChargeCodes = $this->getChargeCodes();
		$chargeCodeData = $allowedChargeCodes[$chargeCode];
		if($chargeCodeData['serviceType'] == 'international' && $country == 'AU') {
			$errors[] = 'International chargecode could not be use for domestic country. Please check and try again.';
		}
		
		if($chargeCodeData['serviceType'] != 'international' && $country != 'AU') {
			$errors[] = 'Domestic chargecode could not be use for international. Please check and try again.';
		}
		
		if($country != 'AU') {
			if((isset($data['has_commercial_value']) && $data['has_commercial_value'] == 1) || (isset($data['product_classification']) && $data['product_classification'] == 991)) {
				if(empty($data['product_classification_text']))
					$errors[] = '<strong>Product Classification text field</strong> is a required field.';
			}
			$country_origin = $data['country_origin'];
			if(!isset($country_origin)) {
				$country_origin = Mage::getStoreConfig('carriers/linksynceparcel/default_country_origin');
			}
			if(empty($country_origin))
				$errors[] = '<strong>Country of Origin</strong> is a required field.';

			if((isset($data['has_commercial_value']) && $data['has_commercial_value'] == 1) && empty($data['hs_tariff']))
				$errors[] = '<strong>HS Tarrif Number</strong> is a required field.';
			
			if(!empty($data['hs_tariff']))
				if(is_numeric($data['hs_tariff'])) {
					$count_digits = strlen($data['hs_tariff']);
					if($count_digits < 6 || $count_digits > 12)
						$errors[] = '<strong>HS Tariffs</strong> must be between 6 - 12 digits.';
				} else {
					$errors[] = '<strong>HS Tariffs</strong> must be a number.';
				}
			
			
			if($data['articles_type'] == 'Custom' && !empty($data['article1'])) {
				$weight = $data['article1']['weight'];
			} else {
				$articles_type = $data['articles_type'];
				$articles = explode('<=>',$articles_type);
				$weight = $articles[1];
			}
			
			// All validated International Articles
			$intArticle = array(
				'Int. Economy Air' 	=> array('weight' => 20, 'insurance' => 5000),
				'Int. Express Courier' => array('weight' => 20, 'insurance' => 5000),
				'Int. Express Courier Document' => array('weight' => 0.5, 'insurance' => 5000),
				'Int. Express Post' => array('weight' => 20, 'insurance' => 5000),
				'Int. Pack & Track' => array('weight' => 2, 'insurance' => 500),
				'Int. Registered' 	=> array('weight' => 2, 'insurance' => 5000),
			);
			
			$isvalidCountries = $this->validCountry();
			if($chargeCodeData['key'] == 'int_pack_track') {
				if(!array_key_exists($country,$isvalidCountries)) {
					$errors[] = 'Pack & Track service is not permitted for this order. Valid countries for Pack & Track service are '. implode(', ', $isvalidCountries);
				}
			}
			
			$label = $chargeCodeData['labelType'];
			if(!empty($intArticle[$label]['weight'])){	
				if($intArticle[$label]['weight'] < $weight) {
					$errors[] = $chargeCodeData['name'] .' reached the maximum article weight of '. $intArticle[$label]['weight'] .'kg.';
				}
			}
			
			if($data['insurance'] == 1 && $intArticle[$label]['insurance'] < $data['insurance_value']) {
				$errors[] = $chargeCodeData['name'] .' reached the maximum insurance value of $'. number_format($intArticle[$label]['insurance'], 2) .'.';
			}
		}
		
		if(count($errors) > 0)
			return $errors;
		
		return false;
	}
	
	public function prepareInternationalReturnAddress($storeId)
	{
		$returnAddressLine2 = trim(Mage::getStoreConfig('carriers/linksynceparcel/return_address_line2', $storeId));
		if(!empty($returnAddressLine2))
		{
			$returnAddressLine2 = '<returnAddressLine2>'.trim($this->xmlData($returnAddressLine2)).'</returnAddressLine2>';
		}
		else
		{
			$returnAddressLine2 = '<returnAddressLine2 />';
		}
		
		$returnAddressLine3 = trim(Mage::getStoreConfig('carriers/linksynceparcel/return_address_line3', $storeId));
		if(!empty($returnAddressLine3))
		{
			$returnAddressLine3 = '<returnAddressLine3>'.trim($this->xmlData($returnAddressLine3)).'</returnAddressLine3>';
		}
		else
		{
			$returnAddressLine3 = '<returnAddressLine3 />';
		}
		
		$returnAddressLine4 = trim(Mage::getStoreConfig('carriers/linksynceparcel/return_address_line4', $storeId));
		if(!empty($returnAddressLine4))
		{
			$returnAddressLine4 = '<returnAddressLine4>'.trim($this->xmlData($returnAddressLine4)).'</returnAddressLine4>';
		}
		else
		{
			$returnAddressLine4 = '<returnAddressLine4 />';
		}
		
		$rphone = trim($this->xmlData(Mage::getStoreConfig('carriers/linksynceparcel/return_phone_number', $storeId)));
		$rphone = preg_replace('/[^0-9]/s', '', $rphone);
		
		$search = array(
			'[[returnAddressLine1]]',
			'[[returnAddressLine2]]',
			'[[returnAddressLine3]]',
			'[[returnAddressLine4]]',
			'[[returnName]]',
			'[[returnPostcode]]',
			'[[returnStateCode]]',
			'[[returnSuburb]]',
			'[[returnCompanyName]]',
			'[[returnEmailAddress]]',
			'[[returnPhoneNumber]]',
		);

		$replace = array(
			trim($this->xmlData(Mage::getStoreConfig('carriers/linksynceparcel/return_address_line1', $storeId))),
			trim($returnAddressLine2),
			trim($returnAddressLine3),
			trim($returnAddressLine4),
			trim($this->xmlData(Mage::getStoreConfig('carriers/linksynceparcel/return_address_name', $storeId))),
			trim(Mage::getStoreConfig('carriers/linksynceparcel/return_address_postcode', $storeId)),
			trim(Mage::getStoreConfig('carriers/linksynceparcel/return_address_statecode', $storeId)),
			trim($this->xmlData(Mage::getStoreConfig('carriers/linksynceparcel/return_address_suburb', $storeId))),
			trim($this->xmlData(Mage::getStoreConfig('carriers/linksynceparcel/return_business_name', $storeId))),
			trim($this->xmlData(Mage::getStoreConfig('carriers/linksynceparcel/return_email_address', $storeId))),
			$rphone,
		);
		
		$template = file_get_contents($this->getTemplatePath().DS.'international-article-return-address-template.xml');
		return str_replace($search, $replace, $template);
	}

	public function prepareInternationalDeliveryAddress($address,$order,$data,$country)
	{
		$street = $address['street'];
		$street = explode("\n", $street);

		$street1 = '<deliveryAddressLine1/>';
		$street2 = '<deliveryAddressLine2/>';
		$street3 = '<deliveryAddressLine3/>';
		$street4 = '<deliveryAddressLine4/>';
		if(isset($street[0]) && !empty($street[0]))
		{
			$street1 = '<deliveryAddressLine1>'.$this->xmlData($street[0]).'</deliveryAddressLine1>';
		}
		if(isset($street[1]) && !empty($street[1]))
		{
			$street2 = '<deliveryAddressLine2>'.$this->xmlData($street[1]).'</deliveryAddressLine2>';
		}
		if(isset($street[2]) && !empty($street[2]))
		{
			$street3 = '<deliveryAddressLine3>'.$this->xmlData($street[2]).'</deliveryAddressLine3>';
		}
		if(isset($street[3]) && !empty($street[3]))
		{
			$street4 = '<deliveryAddressLine4>'.$this->xmlData($street[3]).'</deliveryAddressLine4>';
		}
		
		$city = $address['city'];
		if($country == 'AU') {
			$state = 'NA';
			if($address['region'])
			{
				$state = Mage::helper('linksynceparcel')->getRegion($address['region_id']);
			}
		} else {
			$state = $address['region'];
			$isExist = $this->checkCountryRegion($country);
			if($isExist) {
				$region = Mage::getModel('directory/region')->load($address['region_id']);
				$statecode = $region->getCode();
				$state = $statecode;
			}
			if(empty($state)) {
				$state = 'NA';
			}
		}
		$stateData = ($state == 'NA') ? '<deliveryStateCode/>' : '<deliveryStateCode>'. trim($state) .'</deliveryStateCode>';
		
		$postalCode = $address['postcode'];
		$company = empty($address['company']) ? '<deliveryCompanyName/>' : '<deliveryCompanyName>'.$this->xmlData($address['company']).'</deliveryCompanyName>';
		$firstname = $address['firstname'].' '.$address['lastname'];
		$email = $address['email'];
		$phone = $address['telephone'];
		$phonestr = $phone;
		$phone = $this->getValidPhoneNumber($phone);
		if(!empty($phone)) {
			$withplus = '';
			$strposphone = strpos($phone, '+');
			if($strposphone !== false) {
				$withplus = '+';
			}
			$phone = preg_replace('/[^0-9]/s', '', $phone);
			$phonestr = $withplus . $phone;
		}
		
		$instructions = $data['delivery_instruction'];
		
		$importerCustomsReference = '';
		$senderCustomsReference = '';
		
		$search = array(
			'[[deliveryAddressLine1]]',
			'[[deliveryAddressLine2]]',
			'[[deliveryAddressLine3]]',
			'[[deliveryAddressLine4]]',
			'[[deliveryPhoneNumber]]',
			'[[deliveryCompanyName]]',
			'[[deliveryCountryCode]]',
			'[[deliveryEmailAddress]]',
			'[[deliveryInstructions]]',
			'[[deliveryName]]',
			'[[deliveryPostcode]]',
			'[[deliveryStateCode]]',
			'[[deliverySuburb]]',
			'[[importerCustomsReference]]',
			'[[senderCustomsReference]]',
		);

		$replace = array(
			trim($street1),
			trim($street2),
			trim($street3),
			trim($street4),
			trim($phonestr),
			$company,
			$country,
			trim($email),
			($instructions ? '<deliveryInstructions>'.$this->xmlData(($instructions)).'</deliveryInstructions>' : '<deliveryInstructions />'),
			trim($this->xmlData($firstname)),
			trim($postalCode),
			$stateData,
			trim($this->xmlData($city)),
			(!empty($importerCustomsReference) ? '<importerCustomsReference>'. $importerCustomsReference .'<importerCustomsReference>' : '<importerCustomsReference/>' ),
			(!empty($senderCustomsReference) ? '<senderCustomsReference>'. $senderCustomsReference .'<senderCustomsReference>' : '<senderCustomsReference/>' )
		);
		
		$template = file_get_contents($this->getTemplatePath().DS.'international-article-delivery-address-template.xml');
		return str_replace($search, $replace, $template);
	}
	
	public function prepareInternationalArticles($data,$order,$bulk=false) {
		$articlesInfo = $this->prepareArticles($data,$order,'',true);
		if($bulk){
			$articlesInfo = $this->prepareArticlesBulk($data, $order, true);
		}
		$isInsurance = $data['insurance'];
		$insuranceOrderValue = $data['order_value_insurance'];
		$insuranceValue = $data['insurance_value'];
		$classificationExplanation = $data['product_classification_text'];
		$exportDeclarationNumber = (!empty($data['export_declaration_number']) ? '<exportDeclarationNumber>'. $data['export_declaration_number'] .'</exportDeclarationNumber>' : '<exportDeclarationNumber/>');
		$productClassification = !empty($data['product_classification'])?$data['product_classification']:991;
		$hasCommercialValue = !empty($data['has_commercial_value'])?"true":"false";
		$deliveryFailureDetails = $this->deliveryFailureDetails();
		$articleContents = $this->getOrderProdItems($data, $order->getId(), false, $articlesInfo['total_weight']);
		
		if(empty($insuranceValue)) {
			$insuranceValue = '<insuranceValue/>';
		} else {
			$insuranceValue = '<insuranceValue>'. trim($insuranceValue) .'</insuranceValue>';
		}
		if(!empty($insuranceOrderValue))
			$insuranceValue = '<insuranceValue>'. trim($articleContents['totalcost']) .'</insuranceValue>';
		
		$insuranceValue = ($isInsurance==0)? '<insuranceValue/>' : $insuranceValue;
		
		$search = array(
			'[[preparedarticle]]',
			'[[isInsuranceRequired]]',
			'[[insuranceValue]]',
			'[[classificationExplanation]]',
			'[[exportDeclarationNumber]]',
			'[[productClassification]]',
			'[[hasCommercialValue]]',
			'[[deliveryFailureDetails]]',
			'[[contents]]'
		);

		$replace = array(
			$articlesInfo['info'],
			($isInsurance==0)? 'false' : 'true',
			$insuranceValue,
			$classificationExplanation,
			$exportDeclarationNumber,
			$productClassification,
			$hasCommercialValue,
			$deliveryFailureDetails,
			$articleContents['contents']
		);
		
		$template = file_get_contents($this->getTemplatePath().DS.'international-articleall-template.xml');
		$int_articles_info = str_replace($search, $replace, $template);
		return array('info' => $int_articles_info, 'total_weight' => $articlesInfo['total_weight']);
	}
	
	public function prepareInternationalOrderWeightArticles($data,$order) {
		$articlesInfo = $this->prepareOrderWeightArticles($data,$order,'',true);
		$isInsurance = $data['insurance'];
		$insuranceOrderValue = $data['order_value_insurance'];
		$insuranceValue = $data['insurance_value'];
		$classificationExplanation = $data['product_classification_text'];
		$exportDeclarationNumber = (!empty($data['export_declaration_number']) ? '<exportDeclarationNumber>'. $data['export_declaration_number'] .'</exportDeclarationNumber>' : '<exportDeclarationNumber/>');
		$productClassification = !empty($data['product_classification'])?$data['product_classification']:991;
		$hasCommercialValue = !empty($data['has_commercial_value'])?"true":"false";
		$deliveryFailureDetails = $this->deliveryFailureDetails();
		$articleContents = $this->getOrderProdItems($data, $order->getId(), false, $articlesInfo['total_weight']);
		
		if(empty($insuranceValue)) {
			$insuranceValue = '<insuranceValue/>';
		} else {
			$insuranceValue = '<insuranceValue>'. trim($insuranceValue) .'</insuranceValue>';
		}
		if(!empty($insuranceOrderValue))
			$insuranceValue = '<insuranceValue>'. trim($articleContents['totalcost']) .'</insuranceValue>';
		
		$insuranceValue = ($isInsurance==0)? '<insuranceValue/>' : $insuranceValue;
		
		$search = array(
			'[[preparedarticle]]',
			'[[isInsuranceRequired]]',
			'[[insuranceValue]]',
			'[[classificationExplanation]]',
			'[[exportDeclarationNumber]]',
			'[[productClassification]]',
			'[[hasCommercialValue]]',
			'[[deliveryFailureDetails]]',
			'[[contents]]'
		);
		
		$replace = array(
			$articlesInfo['info'],
			($isInsurance==0)? 'false' : 'true',
			$insuranceValue,
			$classificationExplanation,
			$exportDeclarationNumber,
			$productClassification,
			$hasCommercialValue,
			$deliveryFailureDetails,
			$articleContents['contents']
		);
		
		$template = file_get_contents($this->getTemplatePath().DS.'international-articleall-template.xml');
		$int_articles_info = str_replace($search, $replace, $template);
		return array('info' => $int_articles_info, 'total_weight' => $articlesInfo['total_weight']);
	}
	
	public function deliveryFailureDetails() {
		$addressName = trim($this->xmlData(Mage::getStoreConfig('carriers/linksynceparcel/return_address_name')));
		$companyName = trim($this->xmlData(Mage::getStoreConfig('carriers/linksynceparcel/return_business_name')));
		$addressOne = trim($this->xmlData(Mage::getStoreConfig('carriers/linksynceparcel/return_address_line1')));
		$addressTwo = trim(Mage::getStoreConfig('carriers/linksynceparcel/return_address_line2'));
		$suburb = trim($this->xmlData(Mage::getStoreConfig('carriers/linksynceparcel/return_address_suburb')));
		$stateCode = trim(Mage::getStoreConfig('carriers/linksynceparcel/return_address_statecode'));
		$postCode = trim(Mage::getStoreConfig('carriers/linksynceparcel/return_address_postcode'));
		
		$deliveryFailureDetails = (!empty($addressName) ? '<deliveryFailureName>'. $addressName .'</deliveryFailureName>' : '<deliveryFailureName/>');
		$deliveryFailureDetails .= (!empty($companyName) ? '<deliveryFailureCompanyName>'. $companyName .'</deliveryFailureCompanyName>' : '<deliveryFailureCompanyName/>');
		$deliveryFailureDetails .= '<deliveryFailureAddressLine1>'. $addressOne .'</deliveryFailureAddressLine1>';
		$deliveryFailureDetails .= '<deliveryFailureAddressLine2>'. $addressTwo .'</deliveryFailureAddressLine2>';
		$deliveryFailureDetails .= '<deliveryFailureSuburb>'. $suburb .'</deliveryFailureSuburb>';
		$deliveryFailureDetails .= '<deliveryFailureStateCode>'. $stateCode .'</deliveryFailureStateCode>';
		$deliveryFailureDetails .= '<deliveryFailurePostcode>'. $postCode .'</deliveryFailurePostcode>';
		$deliveryFailureDetails .= '<deliveryFailureCountryCode>AU</deliveryFailureCountryCode>';
		return $deliveryFailureDetails;
	}
	
	public function getOrderProdItems($data, $orderid, $totalonly=false, $totalweight=false) 
	{
		$order = Mage::getModel('sales/order')->load($orderid);
		$ordered_items = $order->getAllItems();
		$orderitems = array();
		if($ordered_items) {
			$pass = false;
			$declared_option_value = 0;
			$singleweight = 0;
			if($totalweight) {
				$singleweight = $this->getContentWeight($ordered_items, $totalweight);
			}
			
			if($data['declared_value'] != 0) {
				$checktotal = 0;
				$totalqty = 0;
				$cntr = 0;
				foreach($ordered_items as $ordereditem) {
					$ischild = $ordereditem->getParentItemId();
					if(!$ischild){
						$qty = $ordereditem->getQtyOrdered();
						$price = $ordereditem->getPrice();
						
						$value = intval($price) * intval($qty);
						
						if($cntr > 0) {
							$totalqty += intval($qty);
						}
							
						$checktotal += $value;
						$cntr++;
					}
				}
				
				if($data['declared_value'] == 1) {
					if($checktotal >= $data['maximum_declared_value']) {
						$pass = true;
						$declared_option_value = intval($data['maximum_declared_value']) - intval($totalqty);
					}
				}
				
				if($data['declared_value'] == 2) {
					$pass = true;
					$declared_option_value = intval($data['fixed_declared_value']) - intval($totalqty);
				}
			}
			
			$alter = false;
			$cnt = 0;
			$contents = '';
			$countryOrigin = !empty($data['country_origin'])?$data['country_origin']:trim(Mage::getStoreConfig('carriers/linksynceparcel/default_country_origin'));
			$hsTariff = !empty($data['hs_tariff'])?$data['hs_tariff']:trim(Mage::getStoreConfig('carriers/linksynceparcel/default_has_tariff'));
			foreach($ordered_items as $ordered_item) {
				$ischild = $ordered_item->getParentItemId();
				if(!$ischild){
					$qty = $ordered_item->getQtyOrdered();
					$price = $ordered_item->getPrice();
					$name = $ordered_item->getName();
					$user_order_details = Mage::getStoreConfig('carriers/linksynceparcel/user_order_details');
					if($user_order_details == 0) {
						$name = Mage::getStoreConfig('carriers/linksynceparcel/default_good_description');
					}
					$weight = $singleweight * $qty;
					if(empty($weight)) {
						$default_article_weight = Mage::getStoreConfig('carriers/linksynceparcel/default_article_weight');

						if($default_article_weight)
						{
							$weight = $default_article_weight;
						} else {
							$weight = 0.00;
						}
					}
					if($price == 0) {
						$price = 0.01;
					}
					$value = intval($price) * intval($qty);
					$totalCost += $value;

					if($pass) {
						if($cnt == 0) {
							$alter = true;
							$maxval = intval($declared_option_value);
							$unitval = $maxval / intval($qty);
							$price = $unitval;
							$value = $maxval;
						}
						
						if($alter && $cnt > 0) {
							$price = 1;
							$value = intval($qty);
						}
					}
					
					
					$contents .= '<content>';
					$contents .= '<goodsDescription>'. trim($this->xmlData($name)) .'</goodsDescription>';
					$contents .= '<quantity>'. intval($qty) .'</quantity>';
					$contents .= '<unitValue>'. number_format($price, 2) .'</unitValue>';
					$contents .= '<value>'. number_format($value, 2) .'</value>';
					$contents .= '<weight>'. $weight .'</weight>';
					$contents .= '<countryOriginCode>'. $countryOrigin .'</countryOriginCode>';
					$contents .= '<hSTariff>'. $hsTariff .'</hSTariff>';
					$contents .= '</content>';
					
					$cnt++;
				}
			}
			
			return ($totalonly)?$totalCost:array('totalcost' => $totalCost, 'contents' => $contents);
		} else {
			return false;
		}
	}
	
	public function getContentWeight($items, $totalweight)
	{
		$cntr = 0;
		foreach($items as $ordered_item) {
			$ischild = $ordered_item->getParentItemId();
			if(!$ischild){
				$cntr += $ordered_item->getQtyOrdered();
			}
		}
		
		$weight = $totalweight/$cntr;
		return $weight;
	}
	
	public function generateDocument($consignmentNumber,$labelContent,$field)
    {
		try
		{
			if($labelContent)
			{
				$name = $consignmentNumber;
				if($field == 'customdocs') {
					$name = 'int_'.$name;
				}
				$filename = $name.'.pdf';
				$filepath = Mage::getBaseDir().DS.'media'.DS.'linksync'.DS.'label'.DS.'consignment'.DS.$filename;
				$handle = fopen($filepath,'wb');
				fwrite($handle, $labelContent);
				fclose($handle);
				Mage::helper('linksynceparcel')->updateConsignmentTable2($consignmentNumber,$field,$filename);
				Mage::helper('linksynceparcel')->updateConsignmentTable2($consignmentNumber,'is_label_created',1);
			}
		}
		catch(Exception $e)
		{
			;//log
		}
	}
	
	public function isInternationalServiceFilter($service) 
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('sales_flat_order');

		$query = "SELECT entity_id FROM {$table} WHERE status='pending' OR status='holded'";
		$results = $readConnection->fetchAll($query);
		if($results && count($results) > 0) {
			$order_ids = array();
			foreach($results as $result) {
				$allowedChargeCodes = $this->getChargeCodes();
				$chargecode = $this->getOrderChargeCode($result['entity_id']);
				$chargeCodeData = $allowedChargeCodes[$chargecode];
				if($chargeCodeData['serviceType'] == $service) {
					$order_ids[] = $result['entity_id'];
				}
			}
			return !empty($order_ids)?$order_ids:false;
		}
		return false;
	}
	
	public function isAppltytoallOptionIsON() 
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('sales_flat_order');
		
		$status_condition = "m.status='pending' OR m.status='holded'";
		$chosen_statuses = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/chosen_statuses');
		if(!empty($chosen_statuses))
		{
			$chosen_statuses = explode(',',$chosen_statuses);
			if(count($chosen_statuses) > 0)
			{
				$status_condition = '';
				foreach($chosen_statuses as $chosen_status)
				{
					if(!empty($chosen_status))
						$status_condition .= 'm.status="'.$chosen_status.'" OR ';
				}

				$status_condition = substr($status_condition, 0, -4);
			}
		}
		

		$query = "SELECT m.entity_id, m.shipping_description, order_address.country_id FROM {$table} AS m LEFT JOIN ". $resource->getTableName('sales_flat_order_address') ." AS order_address ON m.shipping_address_id = order_address.entity_id WHERE ". $status_condition;
		
		$results = $readConnection->fetchAll($query);
		if($results && count($results) > 0) {
			$order_ids = array();
			foreach($results as $result) {
				$entity_id = $result['entity_id'];
				$country_id = $result['country_id'];
				$shipping_description = $result['shipping_description'];
				
				$isDespatched = $this->checkDespatchedConsignment($result['entity_id']);
				if($isDespatched) {
					if($country_id == 'AU') {
						$order_ids[] = $result['entity_id'];
					} else {
						$exist = $this->isMethodExist($shipping_description);
						if($exist) {
							$order_ids[] = $result['entity_id'];
						}
					}
				}
			}
			return !empty($order_ids)?$order_ids:false;
		}
		return false;
	}
	
	public function isMethodExist($method)
	{
		$shipping_method = $this->getNonlinksyncShippingTypeChargecode($method);
		if($shipping_method) {
			return true;
		}
		return false;
	}
	
	public function validCountry()
	{
		$countries = array(
			'BE' => 'Belgium',
			'CA' => 'Canada',
			'CN' => 'China',
			'HR' => 'Croatia',
			'DK' => 'Denmark',
			'EE' => 'Estonia',
			'FR' => 'France',
			'DE' => 'Germany',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IE' => 'Ireland',
			'IL' => 'Israel',
			'KR' => 'Korea, Republic of (South Korea)',
			'LT' => 'Lithuania',
			'MY' => 'Malaysia',
			'MT' => 'Malta',
			'NL' => 'Netherlands',
			'NZ' => 'New Zealand',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'SG' => 'Singapore',
			'SI' => 'Slovenia',
			'ES' => 'Spain',
			'SE' => 'Sweden',
			'GB' => 'United Kingdom',
			'US' => 'USA'
		);
		
		return $countries;
	}
	
	public function getValidPhoneNumber($str) {
		$strpos = strpos($str, ';');
		if($strpos !== false) {
			$strex = explode(';', $str);
			$strpos1 = strpos($strex[1], '&');
			if($strpos1 !== false) {
				$strex1 = explode('&', $strex[1]);
				return $strex1[0];
			}
			return $strex[1];
		}
		return $str;
	}
	
	public function checkDespatchedConsignment($order_id)
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('linksync_linksynceparcel_consignment');

		$query = "select despatched from {$table} where order_id = {$order_id}";
		$results = $readConnection->fetchAll($query);
		if(!empty($results)) {
			$display = array();
			foreach($results as $result) {
				if($result['despatched'] == 1) {
					$display[] = 1;
				}
			}
			if(!empty($display)) {
				return false;
			}
			return true;
		} else {
			return true;
		}
	}

	public function isDisplayConsignmentViewTableShip()
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('linksync_linksynceparcel_nonlinksync');

		$query = "SELECT id FROM {$table}";
		$results = $readConnection->fetchAll($query);
		
		$apply_to_all = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/apply_to_all');
		
		$display = false;
		if($results || $apply_to_all == 1) {
			$display = true;
		}
		return $display;
	}
	
	public function isDisplayConsignmentViewTableLps()
	{
		$display = false;
		$lps_username = trim(Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/lps_username'));
		if(!empty($lps_username)) {
			$display = true;
		}
		return $display;
	}
	
	public function isupgraded()
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('linksync_linksynceparcel_consignment');

		$query = "SELECT * FROM {$table} LIMIT 1";
		$results = $readConnection->fetchAll($query);
		if(!empty($results)) {
			$c = $results[0];
			if(array_key_exists('customdocs',$c)) {
				return true;
			}
			return false;
		}
		return true;
	}

	public function isupgraded_nonlinksync()
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('linksync_linksynceparcel_nonlinksync');

		$query = "SELECT * FROM {$table} LIMIT 1";
		$results = $readConnection->fetchAll($query);
		if(!empty($results)) {
			$c = $results[0];
			if(array_key_exists('service_type',$c)) {
				return true;
			}
			return false;
		}
		return true;
	}
	
	public function checkCountryRegion($countrycode)
	{
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('directory_country_region');

		$query = "SELECT country_id FROM {$table} WHERE country_id='". $countrycode ."'";
		$results = $readConnection->fetchAll($query);
		if(!empty($results)) {
			return true;
		}
		return false;
	}
	
	public function roundoff_number($value, $precision)
	{
		$pow = pow ( 10, $precision ); 
		return ( ceil ( $pow * $value ) + ceil ( $pow * $value - ceil ( $pow * $value ) ) ) / $pow;
	}

	public function calculateWeightDefault($weight)
	{
		if($weight==floatval(0.00))
		{
			return 0.01;
		}

		return $weight;
	}

	public function getCombination($chargecode)
	{
		$chargeCodes = $this->getChargeCodes();
		$cc = $chargeCodes[$chargecode];
		if($cc['serviceCode'] != 0 && $cc['prodCode'] != 0) {
			$sc_pc = $cc['serviceCode'] .'_'. $cc['prodCode'];
			return $this->combinationData($sc_pc);
		}
		return false;
	}

	public function combinationData($sc_pc) 
	{
		$combinations = array(
			'2_93' => array(
				'delivery_signature_allowed' => 1,
				'partial_delivery_allowed' => 0
			),
			'2_96' => array(
				'delivery_signature_allowed' => 1,
				'partial_delivery_allowed' => 0
			),
			'9_91' => array(
				'delivery_signature_allowed' => 0,
				'partial_delivery_allowed' => 1
			),
			'9_87' => array(
				'delivery_signature_allowed' => 0,
				'partial_delivery_allowed' => 1
			)
		);
		if(isset($combinations[$sc_pc])) {	
			return $combinations[$sc_pc];
		}
		return false;
	}

	public function validateCombination($data, $combinations, $chargecode)
	{
		$delivery_signature = $data['delivery_signature_allowed'];
		$partial_delivery = $data['partial_delivery_allowed'];
		if($combinations) {
			if($combinations['delivery_signature_allowed'] != $delivery_signature || $combinations['partial_delivery_allowed'] != $partial_delivery) {
				$pda = ($combinations['partial_delivery_allowed']==1)?'Yes':'No';
				$dsa = ($combinations['delivery_signature_allowed']==1)?'Yes':'No';
				return array('error_msg' => 'You current chargecode <strong>'. $chargecode .'</strong> has invalid combination of data. Please make the <strong>Partial Delivery allowed?</strong> to <strong>'. $pda .'</strong> value and <strong>Delivery signature required?</strong> to <strong>'. $dsa .'</strong> value' );
			}
		}
		return true;
	}

	public function zeroIfEmpty($value)
	{
		if(empty($value))
		{
			return 0;
		}

		return $value;
	}
}