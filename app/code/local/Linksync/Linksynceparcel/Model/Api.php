<?php
if(!defined('LINKSYNC_EPARCEL_URL'))
	define('LINKSYNC_EPARCEL_URL','https://api.linksync.com/linksync/linksyncService');
if(!defined('LINKSYNC_DEBUG'))
	define('LINKSYNC_DEBUG',1);
class Linksync_Linksynceparcel_Model_Api extends Mage_Core_Model_Abstract
{
	public function isAddressValid($address)
	{
		try
		{
			if(is_object($address))
			{
				$country = $address->getCountry();
				if($country == 'AU') {
					$city = $address->getCity();
					$state = Mage::helper('linksynceparcel')->getRegion($address->getRegionId());
					$postcode = $address->getPostcode();
					
					if(LINKSYNC_DEBUG == 1)
					{
						$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL',array('trace'=>1));
					}
					else
					{
						$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL');
					}
					
					$laid = trim(Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/laid'));
					$addressParams = array('suburb' => trim($city), 'postcode' => trim($postcode), 'stateCode' => trim($state));
					
					$stdClass = $client->isAddressValid($laid,$addressParams); 

					if($stdClass)
					{
						if(LINKSYNC_DEBUG == 1)
						{
							Mage::log('isAddressValid Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
							Mage::log('isAddressValid Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
						}
						return 1;
					}
				} else {
					return 1;
				}
			}
		}
		catch(Exception $e)
		{
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('isAddressValid Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('isAddressValid Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
			return $e->getMessage();
		}
	}
	
	public function getWebserviceUrl($next = false)
	{
		return LINKSYNC_EPARCEL_URL;
	}
	
	public function createConsignment($article,$loop=0,$chargeCode=false)
	{
		if($loop < 2)
		{
			try
			{
				if(LINKSYNC_DEBUG == 1)
				{
					$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL',array('trace'=>1));
				}
				else
				{
					$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL');
				}
				
				Mage::log('Articles: '.preg_replace('/\s+/', ' ', trim($article)), null, 'linksync_eparcel.log', true);
				
				$laid = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/laid');
				
				$chargeCodeData = Mage::helper('linksynceparcel')->getChargeCodes();
				$codeData = $chargeCodeData[$chargeCode];
				if($codeData['serviceType'] == 'international') {
					$arg3 = 'A4-1pp';
					$arg4 = 'true';
					$arg5 = 0;
					$arg6 = 0;
				} else {
					$service = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/'. $codeData['key']);
					$labelType = explode('_', $service);
					$arg3 = $labelType[0];
					$arg4 = ($labelType[1]==0)?'false':'true';
					$arg5 = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/'. $codeData['key'] .'_left_offset');
					$arg6 = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/'. $codeData['key'] .'_top_offset');
				}
				
				$site_url = Mage::helper('linksynceparcel')->getSiteUrl(true);
				
				$stdClass = $client->createConsignment2($laid,$article,$site_url,$arg3,$arg4,$arg5,$arg6); 
	
				if($stdClass)
				{
					if(LINKSYNC_DEBUG == 1)
					{
						$last_req = $client->__getLastRequest();
						$c_last_req = $this->removeEncodedData($last_req, array('arg1'));
						Mage::log('createConsignment Request: '.$c_last_req, null, 'linksync_eparcel.log', true);
						
						$last_res = $client->__getLastResponse();
						$c_last_res = $this->removeEncodedData($last_res, array('lpsLabels'));
						Mage::log('createConsignment Response: '.$c_last_res, null, 'linksync_eparcel.log', true);
					}
					return $stdClass;
				}
			}
			catch(Exception $e)
			{
				if(LINKSYNC_DEBUG == 1 && $client)
				{
					Mage::log('createConsignment Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
					Mage::log('createConsignment Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
				}
				Mage::log('createConsignment Error catch from API class: '.$e->getMessage(), null, 'linksync_eparcel.log', true);
				throw $e;
			}
		}
	}
	
	public function modifyConsignment($article,$consignmentNumber,$chargeCode)
	{
		try
		{
			if(LINKSYNC_DEBUG == 1)
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL',array('trace'=>1));
			}
			else
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL');
			}
			
			Mage::log('Modified Articles: '.preg_replace('/\s+/', ' ', trim($article)), null, 'linksync_eparcel.log', true);
			
			$laid = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/laid');
			$site_url = Mage::helper('linksynceparcel')->getSiteUrl(true);
			
			$chargeCodeData = Mage::helper('linksynceparcel')->getChargeCodes();
			$codeData = $chargeCodeData[$chargeCode];
			if($codeData['serviceType'] == 'international') {
				$arg3 = 'A4-1pp';
				$arg4 = 'true';
				$arg5 = 0;
				$arg6 = 0;
			} else {
				$service = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/'. $codeData['key']);
				$labelType = explode('_', $service);
				$arg3 = $labelType[0];
				$arg4 = ($labelType[1]==0)?'false':'true';
				$arg5 = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/'. $codeData['key'] .'_left_offset');
				$arg6 = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/'. $codeData['key'] .'_top_offset');
			}
			
			$stdClass = $client->modifyConsignment2($laid,$consignmentNumber,$article,$site_url,$arg3,$arg4,$arg5,$arg6);

			if($stdClass)
			{
				if(LINKSYNC_DEBUG == 1)
				{
					$last_req = $client->__getLastRequest();
					$c_last_req = $this->removeEncodedData($last_req, array('arg2'));
					Mage::log('modifyConsignment Request: '.$c_last_req, null, 'linksync_eparcel.log', true);
					
					$last_res = $client->__getLastResponse();
					$c_last_res = $this->removeEncodedData($last_res, array('lpsLabels'));
					Mage::log('modifyConsignment Response: '.$c_last_res, null, 'linksync_eparcel.log', true);
				}
				return $stdClass;
			}
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('modifyConsignment Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('modifyConsignment Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
		}
		catch(Exception $e)
		{
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('modifyConsignment Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('modifyConsignment Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
			throw $e;
		}
	}
	
	public function unAssignConsignment($consignmentNumber)
	{
		try
		{
			if(LINKSYNC_DEBUG == 1)
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL',array('trace'=>1));
			}
			else
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL');
			}
			
			$laid = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/laid');
			
			$stdClass = $client->unAssignConsignment($laid,$consignmentNumber);

			if($stdClass)
			{
				if(LINKSYNC_DEBUG == 1)
				{
					Mage::log('unAssignConsignment Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
					Mage::log('unAssignConsignment Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
				}
				return $stdClass;
			}
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('unAssignConsignment Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('unAssignConsignment Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
		}
		catch(Exception $e)
		{
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('unAssignConsignment Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('unAssignConsignment Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
			throw $e;
		}
	}
	
	public function deleteConsignment($consignmentNumber)
	{
		try
		{
			if(LINKSYNC_DEBUG == 1)
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL',array('trace'=>1));
			}
			else
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL');
			}
			
			$laid = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/laid');
			$site_url = Mage::helper('linksynceparcel')->getSiteUrl(true);
			
			$stdClass = $client->deleteConsignment($laid,$consignmentNumber,$site_url);

			if($stdClass)
			{
				if(LINKSYNC_DEBUG == 1)
				{
					Mage::log('deleteConsignment Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
					Mage::log('deleteConsignment Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
				}
				return $stdClass;
			}
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('deleteConsignment Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('deleteConsignment Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
		}
		catch(Exception $e)
		{
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('deleteConsignment Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('deleteConsignment Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
			throw $e;
		}
	}
	
	public function assignConsignmentToManifest($consignmentNumber)
	{
		try
		{
			if(LINKSYNC_DEBUG == 1)
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL',array('trace'=>1));
			}
			else
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL');
			}
			
			$laid = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/laid');
			
			$stdClass = $client->assignConsignmentToManifest($laid,$consignmentNumber);

			if($stdClass)
			{
				if(LINKSYNC_DEBUG == 1)
				{
					Mage::log('assignConsignmentToManifest Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
					Mage::log('assignConsignmentToManifest Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
				}
				return $stdClass;
			}
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('assignConsignmentToManifest Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('assignConsignmentToManifest Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
		}
		catch(Exception $e)
		{
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('assignConsignmentToManifest Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('assignConsignmentToManifest Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
			throw $e;
		}
	}
	
	public function seteParcelMerchantDetails()
	{
		try
		{
			$this->getWebserviceUrl(true).'?WSDL';
	
			if(LINKSYNC_DEBUG == 1)
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL',array('trace'=>1));
			}
			else
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL');
			}
			
			$laid = trim(Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/laid'));
			$merchant_location_id = trim(Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/merchant_location_id'));
			$post_charge_to_account = trim(Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/post_charge_to_account'));
			$sftp_username = trim(Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/sftp_username'));
			$sftp_password = trim(Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/sftp_password'));
			$operation_mode = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/operation_mode');
			if($operation_mode == 2)
			{
				$operation_mode = 'test';
			}
			else
			{
				$operation_mode = 'live';
			}
			
			$label_logo = '';

			$merchant_id = trim(Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/merchant_id'));
			$lodgement_facility = trim(Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/lodgement_facility'));
			
			$lps_username = trim(Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/lps_username'));
			$lps_password = trim(Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/lps_password'));
			
			$site_url = Mage::helper('linksynceparcel')->getSiteUrl(true);
			
			$stdClass = $client->seteParcelMerchantDetails($laid,$merchant_location_id, $post_charge_to_account,$sftp_username,$sftp_password, $operation_mode, '', $merchant_id, $lodgement_facility, $label_logo,$lps_username,$lps_password,$site_url ); 

			if($stdClass)
			{
				if(LINKSYNC_DEBUG == 1)
				{
					Mage::log('seteParcelMerchantDetails Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
					Mage::log('seteParcelMerchantDetails Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
				}
				return $stdClass;
			}
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('seteParcelMerchantDetails Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('seteParcelMerchantDetails Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
		}
		catch(Exception $e)
		{
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('seteParcelMerchantDetails Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('seteParcelMerchantDetails Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
			throw $e;
		}
	}
	
	public function setReturnAddress()
	{
		try
		{
			$this->getWebserviceUrl(true).'?WSDL';
	
			if(LINKSYNC_DEBUG == 1)
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL',array('trace'=>1));
			}
			else
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL');
			}
			
			$returnAddress = array();
			$returnAddress['returnAddressLine1'] = trim(Mage::getStoreConfig('carriers/linksynceparcel/return_address_line1'));
			$returnAddress['returnAddressLine2'] = trim(Mage::getStoreConfig('carriers/linksynceparcel/return_address_line2'));
			$returnAddress['returnAddressLine3'] = trim(Mage::getStoreConfig('carriers/linksynceparcel/return_address_line3'));
			$returnAddress['returnAddressLine4'] = trim(Mage::getStoreConfig('carriers/linksynceparcel/return_address_line4'));
			$returnAddress['returnCountryCode'] = 'AU';
			$returnAddress['returnName'] = trim(Mage::getStoreConfig('carriers/linksynceparcel/return_address_name'));
			$returnAddress['returnPostcode'] = trim(Mage::getStoreConfig('carriers/linksynceparcel/return_address_postcode'));
			$returnAddress['returnStateCode'] = trim(Mage::getStoreConfig('carriers/linksynceparcel/return_address_statecode'));
			$returnAddress['returnSuburb'] = trim(Mage::getStoreConfig('carriers/linksynceparcel/return_address_suburb'));
			
			$returnAddress['returnCompanyName'] = trim(Mage::getStoreConfig('carriers/linksynceparcel/return_business_name'));
			$returnAddress['returnEmailAddress'] = trim(Mage::getStoreConfig('carriers/linksynceparcel/return_email_address'));
			$returnAddress['returnPhoneNumber'] = trim(Mage::getStoreConfig('carriers/linksynceparcel/return_phone_number'));
			
			$laid = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/laid');
			
			$stdClass = $client->setReturnAddress($laid,$returnAddress); 

			if($stdClass)
			{
				if(LINKSYNC_DEBUG == 1)
				{
					Mage::log('setReturnAddress Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
					Mage::log('setReturnAddress Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
				}
				return $stdClass;
			}
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('setReturnAddress Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('setReturnAddress Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
		}
		catch(Exception $e)
		{
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('setReturnAddress Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('setReturnAddress Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
			throw $e;
		}
	}
	
	public function getArticles($consignmentNumber)
	{
		try
		{
			if(LINKSYNC_DEBUG == 1)
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL',array('trace'=>1));
			}
			else
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL');
			}
			
			$laid = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/laid');
			
			$stdClass = $client->getArticles($laid,$consignmentNumber); 

			if($stdClass)
			{
				if(LINKSYNC_DEBUG == 1)
				{
					Mage::log('getArticles Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
					Mage::log('getArticles Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
				}
				return $stdClass;
			}
			
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('getArticles Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('getArticles Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
		}
		catch(Exception $e)
		{
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('getArticles Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('getArticles Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
			throw $e;
		}
	}
	
	public function getManifest()
	{
		try
		{
			if(LINKSYNC_DEBUG == 1)
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL',array('trace'=>1));
			}
			else
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL');
			}
			
			$laid = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/laid');
			
			$stdClass = $client->getManifest($laid); 

			if($stdClass)
			{
				if(LINKSYNC_DEBUG == 1)
				{
					Mage::log('getManifest Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
					//Mage::log('getManifest Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
				}
				return $stdClass;
			}
			
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('getManifest Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('getManifest Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
		}
		catch(Exception $e)
		{
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('getManifest Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('getManifest Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
			throw $e;
		}
	}
	
	public function getNotDespatchedConsignments()
	{
		try
		{
			if(LINKSYNC_DEBUG == 1)
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL',array('trace'=>1));
			}
			else
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL');
			}
			
			$laid = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/laid');
			
			$stdClass = $client->getNotDespatchedConsignments($laid); 

			if($stdClass)
			{
				if(LINKSYNC_DEBUG == 1)
				{
					Mage::log('getNotDespatchedConsignments  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
					Mage::log('getNotDespatchedConsignments  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
				}
				return $stdClass->consignments;
			}
			
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('getNotDespatchedConsignments  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('getNotDespatchedConsignments  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
		}
		catch(Exception $e)
		{
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				@Mage::log('getNotDespatchedConsignmentsResponse  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				@Mage::log('getNotDespatchedConsignmentsResponse  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
			return $e->getMessage();
		}
	}
	
	public function getReturnLabels()
	{
		try
		{
			if(LINKSYNC_DEBUG == 1)
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL',array('trace'=>1));
			}
			else
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL');
			}
			
			$laid = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/laid');
			$labelType = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/label_format');
			$stdClass = $client->getReturnLabels($laid,$labelType); 

			if($stdClass)
			{
				if(LINKSYNC_DEBUG == 1)
				{
					Mage::log('getReturnLabels  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
					//Mage::log('getReturnLabels  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
				}
				return $stdClass;
			}
			
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('getReturnLabels  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('getReturnLabels  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
		}
		catch(Exception $e)
		{
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('getReturnLabels  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('getReturnLabels  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
			throw $e;
		}
	}
	
	public function getLabelsByConsignments($consignments,$chargeCode)
	{
		try
		{
			if(LINKSYNC_DEBUG == 1)
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL',array('trace'=>1));
			}
			else
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL');
			}
			
			$laid = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/laid');
			$chargeCodeData = Mage::helper('linksynceparcel')->getChargeCodes();
			$codeData = $chargeCodeData[$chargeCode];
			if($codeData['serviceType'] == 'international') {
				$arg3 = 'A4-1pp';
				$arg4 = 'true';
				$arg5 = 0;
				$arg6 = 0;
			} else {
				$service = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/'. $codeData['key']);
				$labelType = explode('_', $service);
				$arg3 = $labelType[0];
				$arg4 = ($labelType[1]==0)?'false':'true';
				$arg5 = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/'. $codeData['key'] .'_left_offset');
				$arg6 = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/'. $codeData['key'] .'_top_offset');
			}
			
			$stdClass = $client->getLabelsByConsignments($laid,explode(',',$consignments),$arg3,$arg4,$arg5,$arg6); 

			if($stdClass)
			{
				if(LINKSYNC_DEBUG == 1)
				{
					Mage::log('getLabelsByConsignments  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
					//Mage::log('getLabelsByConsignments  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
				}
				return $stdClass;
			}
			
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('getLabelsByConsignments  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('getLabelsByConsignments  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
		}
		catch(Exception $e)
		{
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('getLabelsByConsignments  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('getLabelsByConsignments  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
			throw $e;
		}
	}
	
	public function getLabelsByInternationalConsignments($consignments,$chargeCode)
	{
		try
		{
			if(LINKSYNC_DEBUG == 1)
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL',array('trace'=>1));
			}
			else
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL');
			}
			
			$laid = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/laid');
			$chargeCodeData = Mage::helper('linksynceparcel')->getChargeCodes();
			$codeData = $chargeCodeData[$chargeCode];
			if($codeData['serviceType'] == 'international') {
				$arg3 = 'A4-1pp';
				$arg4 = 'true';
				$arg5 = 0;
				$arg6 = 0;
			} else {
				$service = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/'. $codeData['key']);
				$labelType = explode('_', $service);
				$arg3 = $labelType[0];
				$arg4 = ($labelType[1]==0)?'false':'true';
				$arg5 = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/'. $codeData['key'] .'_left_offset');
				$arg6 = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/'. $codeData['key'] .'_top_offset');
			}
			
			$stdClass = $client->getLabelsByConsignments($laid,explode(',',$consignments),$arg3,$arg4,$arg5,$arg6,'true'); 

			if($stdClass)
			{
				if(LINKSYNC_DEBUG == 1)
				{
					Mage::log('getLabelsByConsignments  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
					//Mage::log('getLabelsByConsignments  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
				}
				return $stdClass;
			}
			
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('getLabelsByConsignments  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('getLabelsByConsignments  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
		}
		catch(Exception $e)
		{
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('getLabelsByConsignments  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('getLabelsByConsignments  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
			throw $e;
		}
	}
	
	public function getReturnLabelsByConsignments($consignments)
	{
		try
		{
			if(LINKSYNC_DEBUG == 1)
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL',array('trace'=>1));
			}
			else
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL');
			}
			
			$laid = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/laid');
			$labelType = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/label_format');
			
			$stdClass = $client->getReturnLabelsByConsignments($laid,explode(',',$consignments),$labelType); 

			if($stdClass)
			{
				if(LINKSYNC_DEBUG == 1)
				{
					Mage::log('getReturnLabelsByConsignments  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
					//Mage::log('getReturnLabelsByConsignments  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
				}
				return $stdClass;
			}
			
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('getReturnLabelsByConsignments  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('getReturnLabelsByConsignments  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
		}
		catch(Exception $e)
		{
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('getReturnLabelsByConsignments  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('getReturnLabelsByConsignments  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
			throw $e;
		}
	}
	
	public function despatchManifest()
	{
		try
		{
			if(LINKSYNC_DEBUG == 1)
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL',array('trace'=>1));
			}
			else
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL');
			}
			
			$laid = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/laid');
			$site_url = Mage::helper('linksynceparcel')->getSiteUrl(true);
			
			$stdClass = $client->despatchManifest($laid,$site_url); 

			if($stdClass)
			{
				if(LINKSYNC_DEBUG == 1)
				{
					Mage::log('despatchManifest  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
					Mage::log('despatchManifest  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
				}
				return $stdClass;
			}
			
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('despatchManifest  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('despatchManifest  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
		}
		catch(Exception $e)
		{
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('despatchManifest  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('despatchManifest  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
			throw $e;
		}
	}
	
	public function printManifest($manifestNumber)
	{
		try
		{
			if(LINKSYNC_DEBUG == 1)
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL',array('trace'=>1));
			}
			else
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL');
			}
			
			$laid = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/laid');
			
			$stdClass = $client->printManifest($laid,$manifestNumber); 

			if($stdClass)
			{
				if(LINKSYNC_DEBUG == 1)
				{
					Mage::log('printManifest  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
					//Mage::log('printManifest  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
				}
				return $stdClass;
			}
			
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('printManifest  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('printManifest  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
		}
		catch(Exception $e)
		{
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('printManifest  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('printManifest  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
			throw $e;
		}
	}
	
	public function sendLog($manifestNumber)
	{
		try
		{
			if(LINKSYNC_DEBUG == 1)
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL',array('trace'=>1));
			}
			else
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL');
			}
			
			$laid = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/laid');
			
	    	$file = Mage::getBaseDir().DS.'var'.DS.'log'.DS.'linksync_eparcel_log_'.date('Ymdhis').'.zip';
			
			if(Mage::helper('linksynceparcel')->createZip(__DIR__.'/../../../../../../var/log/linksync_eparcel.log',$file))
			{
				$stdClass = $client->sendLogFile($laid,file_get_contents($file)); 
	
				if($stdClass)
				{
					if(LINKSYNC_DEBUG == 1)
					{
						//Mage::log('sendLogFile  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
						Mage::log('sendLogFile  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
					}
					return $stdClass;
				}
				
				if(LINKSYNC_DEBUG == 1 && $client)
				{
					Mage::log('sendLogFile  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
					Mage::log('sendLogFile  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
				}
			}
			else
			{
				throw new Exception('Failed to create archive file');
			}
		}
		catch(Exception $e)
		{
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('sendLogFile  Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('sendLogFile  Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
			throw $e;
		}
	}
	
	public function getVersionNumber()
	{
		try
		{
			if(LINKSYNC_DEBUG == 1)
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL',array('trace'=>1));
			}
			else
			{
				$client = new SoapClient($this->getWebserviceUrl(true).'?WSDL');
			}
			
			$laid = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/laid');
			$stdClass = $client->getVersionNumber($laid); 

			if($stdClass)
			{
				if(LINKSYNC_DEBUG == 1)
				{
					Mage::log('getVersionNumber Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
					Mage::log('getVersionNumber Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
				}
				return $stdClass;
			}
		}
		catch(Exception $e)
		{
			if(LINKSYNC_DEBUG == 1 && $client)
			{
				Mage::log('getVersionNumber Request: '.$client->__getLastRequest(), null, 'linksync_eparcel.log', true);
				Mage::log('getVersionNumber Response: '.$client->__getLastResponse(), null, 'linksync_eparcel.log', true);
			}
			return $e->getMessage();
		}
	}
	
	public function removeEncodedData($string,$tags)
	{
		return preg_replace('#<(' . implode( '|', $tags) . ')(?:[^>]+)?>.*?</\1>#s', '', $string);
	}
}