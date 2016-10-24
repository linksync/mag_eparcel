<?php
class Linksync_Linksynceparcel_Model_Observer
{
	public function saveOrderAfterSubmit(Varien_Event_Observer $observer)
	{
		$active = (int)Mage::getStoreConfig('carriers/linksynceparcel/active');
		if($active == 1)
		{
			try
			{
				$quote = $observer['quote'];
				if($quote->getIsMultiShipping())
				{
					$orders = $observer['orders'];
					foreach($orders as $order)
					{
						if(is_object($order) && $order->getId() > 0)
						{
							Mage::log('Observer, setAuthority On Multi-shipping: '.$order->getId(), null, 'linksync_eparcel.log', true);
							$code = Mage::helper('linksynceparcel')->getOrderCarrier($order->getId());
							if($code == 'linksynceparcel')
							{
								$signatureRequired = Mage::getStoreConfig('carriers/linksynceparcel/signature_required');
								if(!$signatureRequired)
								{
									$authority_to_leave = Mage::getSingleton('checkout/session')->getLinksyncLinksynceparcelAuthorityToLeaveSession();
									$special_instructions = Mage::getSingleton('checkout/session')->getLinksyncLinksynceparcelSpecialInstructionsSession();
									Mage::helper('linksynceparcel')->setAuthority($order->getId(), $special_instructions);								
								}
								Mage::helper('linksynceparcel')->isOrderAddressValid($order->getId());
							}
						}
					}
					Mage::getSingleton('checkout/session')->setLinksyncLinksynceparcelAuthorityToLeaveSession('');
					Mage::getSingleton('checkout/session')->setLinksyncLinksynceparcelSpecialInstructionsSession('');
					Mage::getSingleton('checkout/session')->unsetLinksyncLinksynceparcelAuthorityToLeaveSession();
					Mage::getSingleton('checkout/session')->unsetLinksyncLinksynceparcelSpecialInstructionsSession();
				}
				else
				{
					$order = $observer['order'];
					if(is_object($order) && $order->getId() > 0)
					{
						Mage::log('Observer, setAuthority: '.$order->getId(), null, 'linksync_eparcel.log', true);
						$code = Mage::helper('linksynceparcel')->getOrderCarrier($order->getId());
						if($code == 'linksynceparcel')
						{
							$signatureRequired = Mage::getStoreConfig('carriers/linksynceparcel/signature_required');
							if(!$signatureRequired)
							{
								$authority_to_leave = Mage::getSingleton('checkout/session')->getLinksyncLinksynceparcelAuthorityToLeaveSession();
								$special_instructions = Mage::getSingleton('checkout/session')->getLinksyncLinksynceparcelSpecialInstructionsSession();
								Mage::helper('linksynceparcel')->setAuthority($order->getId(), $special_instructions);
								
								Mage::getSingleton('checkout/session')->setLinksyncLinksynceparcelAuthorityToLeaveSession('');
								Mage::getSingleton('checkout/session')->setLinksyncLinksynceparcelSpecialInstructionsSession('');
								Mage::getSingleton('checkout/session')->unsetLinksyncLinksynceparcelAuthorityToLeaveSession();
								Mage::getSingleton('checkout/session')->unsetLinksyncLinksynceparcelSpecialInstructionsSession();
							}
							
							Mage::helper('linksynceparcel')->isOrderAddressValid($order->getId());
						}
					}
				}
			}
			catch(Exception $e)
			{
				Mage::getSingleton('checkout/session')->setLinksyncLinksynceparcelAuthorityToLeaveSession('');
				Mage::getSingleton('checkout/session')->setLinksyncLinksynceparcelSpecialInstructionsSession('');
				Mage::getSingleton('checkout/session')->unsetLinksyncLinksynceparcelAuthorityToLeaveSession();
				Mage::getSingleton('checkout/session')->unsetLinksyncLinksynceparcelSpecialInstructionsSession();
				Mage::log('Observer:'.$e->getMessage(), null, 'linksync_eparcel.log', true);
			}
		}
	}
	
	public function resubmitConsignment(Varien_Event_Observer $observer)
	{
		$active = (int)Mage::getStoreConfig('carriers/linksynceparcel/active');
		if($active == 1)
		{
			try
			{
				$order_id = (int)$observer['order'];
				if($order_id > 0)
				{
					$order = Mage::getModel('sales/order')->load($order_id);
					$code = Mage::helper('linksynceparcel')->getOrderCarrier($order_id);
					if($code == 'linksynceparcel')
					{
						Mage::helper('linksynceparcel')->isOrderAddressValid($order->getId(),true);
						
						if($order->getIsAddressValid())
						{
							$consignments = Mage::helper('linksynceparcel')->getConsignments($order_id);
							if($consignments && count($consignments) > 0)
							{
								$success = 0;
								foreach($consignments as $consignment)
								{
									$consignment_number = $consignment['consignment_number'];
									if($consignment['is_next_manifest'])
									{
										try
										{
											if(Mage::helper('linksynceparcel')->resubmitConsignment($order_id, $consignment_number))
											{
												Mage::helper('linksynceparcel')->removeConsignmentLabels($consignment_number);
												$message = 'resubmitConsignment #'.$consignment_number.': Submitted successfully';
												Mage::getModel('adminhtml/session')->addSuccess($message);
												Mage::log($message, null, 'linksync_eparcel.log', true);
											}
										}
										catch(Exception $e2)
										{
											$message = 'resubmitConsignment #'.$consignment_number.': Error => '.$e2->getMessage();
											Mage::getModel('adminhtml/session')->addError($message);
											Mage::log($message, null, 'linksync_eparcel.log', true);
										}
									}
								}
								Mage::getModel('core/config')->saveConfig('carriers/linksynceparcel/manifest_sync', 1);
							}
						}
					}
				}
			}
			catch(Exception $e)
			{
				$message = 'resubmitConsignment:'.$e->getMessage();
				Mage::getModel('adminhtml/session')->addError($message);
				Mage::log($message, null, 'linksync_eparcel.log', true);
			}
		}
	}
	
	public function configSection(Varien_Event_Observer $observer)
	{
		$active = (int)Mage::getStoreConfig('carriers/linksynceparcel/active');
		if($active == 1)
		{
			try
			{
				Mage::getModel('linksynceparcel/api')->seteParcelMerchantDetails();
			}
			catch(Exception $e)
			{
				$message = 'Updating Merchant Details, Error:'.$e->getMessage();
				Mage::getSingleton('adminhtml/session')->addError($message);
				Mage::log($message, null, 'linksync_eparcel.log', true);
			}
			
			try
			{
				Mage::getModel('linksynceparcel/api')->setReturnAddress();		
			}
			catch(Exception $e)
			{
				$message = 'Set Return Address, Error:'.$e->getMessage();
				Mage::getSingleton('adminhtml/session')->addError($message);
				Mage::log($message, null, 'linksync_eparcel.log', true);
			}
			
			// Save Chargecode services
			$services = array(
				'parcel_post' => 'Parcel Post',
				'express_post' => 'Express Post eParcel',
				'int_economy_air' => 'Int. Economy Air',
				'int_express_courier' => 'Int. Express Courier Document',
				'int_express_post' => 'Int. Express Post',
				'int_pack_track' => 'Int. Pack & Track',
				'int_registered' => 'Int. Registered',
			);
			foreach($services as $k=>$service) {
				Mage::helper('linksynceparcel')->updateServiceData($k);
			}
			
			$laid = trim(Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/laid'));
			$merchant_location_id = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/merchant_location_id');
			
			if( (Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/checklaid') != $laid) || (Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/check_merchant_location_id') != $merchant_location_id) )
			{
				//delete articles from db
				//delete consignments from db
				//delete manifest from db
				//delete labels from file system
				
				Mage::getModel('core/config')->saveConfig('carriers/linksynceparcel/checklaid', trim($laid));
				Mage::getModel('core/config')->saveConfig('carriers/linksynceparcel/check_merchant_location_id', trim($merchant_location_id));
			}
		}
	}
	
	public function orderCancelled(Varien_Event_Observer $observer)
	{
		$active = (int)Mage::getStoreConfig('carriers/linksynceparcel/active');
		if($active == 1)
		{
			try
			{
				$order = $observer->getPayment()->getOrder();
				if(is_object($order) && $order->getId() > 0)
				{
					Mage::log($order->getIncrementId().' is cancelled.', null, 'linksync_eparcel.log', true);
					$consignments = Mage::helper('linksynceparcel')->getConsignments($order->getId());
					if($consignments && count($consignments) > 0)
					{
						foreach($consignments as $consignment)
						{
							$consignmentNumber = $consignment['consignment_number'];
							$ok = Mage::getModel('linksynceparcel/api')->deleteConsignment($consignmentNumber);
							if($ok)
							{
								$filename = $consignmentNumber.'.pdf';
								$filepath = Mage::getBaseDir().DS.'media'.DS.'linksync'.DS.'label'.DS.'consignment'.DS.$filename;
								if(file_exists($filepath))
								{
									unlink($filepath);
								}
								
								$filepath2 = Mage::getBaseDir().DS.'media'.DS.'linksync'.DS.'label'.DS.'returnlabels'.DS.$filename;
								if(file_exists($filepath2))
								{
									unlink($filepath2);
								}
								
								Mage::helper('linksynceparcel')->deleteConsignment($order->getId(),$consignmentNumber);
							}
						}
						Mage::helper('linksynceparcel')->getManifestNumber();
					}
				}
				Mage::helper('linksynceparcel')->deleteManifest();
			}
			catch(Exception $e)
			{
				Mage::helper('linksynceparcel')->deleteManifest();
				Mage::log('orderCancelled Observer: '.$e->getMessage(), null, 'linksync_eparcel.log', true);
			}
		}
	}
	
	public function saveMultiship(Varien_Event_Observer $observer)
	{
		$active = (int)Mage::getStoreConfig('carriers/linksynceparcel/active');
		if($active == 1)
		{
			try
			{
				if(!Mage::getStoreConfig('carriers/linksynceparcel/signature_required'))
				{
					$eparcelExist = false;
					
					$request = $observer['request'];
					$data = $request->getPost('shipping_method', '');
					foreach($data as $datum)
					{
						$code = 'nomethod';
						if(!empty($datum))
						{
							$methods = Mage::helper('linksynceparcel')->collectShippingData($datum);
							if(is_array($methods) && isset($methods[0]))
							{
								$code = $methods[0];
							}
						}
						
						if($code == 'linksynceparcel')
						{
							$eparcelExist = true;
							break;
						}
					}
					
					if($eparcelExist)
					{
						$authority_to_leave	= $request->getPost('authority_to_leave', '');
						$special_instructions = $request->getPost('special_instructions', '');
						Mage::getSingleton('checkout/session')->setLinksyncLinksynceparcelAuthorityToLeaveSession($authority_to_leave);
						Mage::getSingleton('checkout/session')->setLinksyncLinksynceparcelSpecialInstructionsSession(base64_encode($special_instructions));
					}
				}
			}
			catch(Exception $e)
			{
				Mage::log('saveMultiship exception:'.$e->getMessage(), null, 'linksync_eparcel.log', true);
			}
		}
	}
	
	public function logPurge()
	{
		$active = (int)Mage::getStoreConfig('carriers/linksynceparcel/active');
		if($active == 1)
		{
			$lines = 10000;
			$buffer = 4096;
			$file = __DIR__.'/../../../../../../var/log/linksync_eparcel.log';
			
			$output = '';
			$chunk = '';
			
			$f = @fopen($file, "rb");
			if ($f === false)
				return false;
	
			fseek($f, -1, SEEK_END);
			if (fread($f, 1) != "\n")
				$lines -= 1;
	
			while (ftell($f) > 0 && $lines >= 0) 
			{
				$seek = min(ftell($f), $buffer);
				fseek($f, -$seek, SEEK_CUR);
				$output = ($chunk = fread($f, $seek)) . $output;
				fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
				$lines -= substr_count($chunk, "\n");
			}
			 
			while ($lines++ < 0)
			{
				$output = substr($output, strpos($output, "\n") + 1);
			}
			fclose($f);
			$content = trim($output);
			$f = @fopen($file, "w");
			if ($f === false)
				return false;
			fwrite($f,$content);
			fclose($f);
			exit;
		}
	}
	
	public function orderSave(Varien_Event_Observer $observer)
	{
		$active = (int)Mage::getStoreConfig('carriers/linksynceparcel/active');
		if($active == 1)
		{
			try 
			{
				$order = $observer->getOrder();
				Mage::log('orderSave:'.$order->getId(), null, 'linksync_eparcel.log', true);
				if($order && $order->getId() > 0)
				{
					if(!$order->getIsAddressValid())
					{
						$address = $order->getShippingAddress();
						$status = Mage::getSingleton('linksynceparcel/api')->isAddressValid($address);
						
						$resource = Mage::getSingleton('core/resource');
						$writeConnection = $resource->getConnection('core_write');
						$table = $resource->getTableName('sales_flat_order');
						if($status == 1)
						{
							$query = "UPDATE {$table} SET is_address_valid=1 WHERE entity_id=".$order->getId();
						}
						else
						{
							$query = "UPDATE {$table} SET is_address_valid=0 WHERE entity_id=".$order->getId();
						}
						Mage::log('orderSave:'.$query, null, 'linksync_eparcel.log', true);
						$writeConnection->query($query);
					}
				}
			}
			catch(Exception $e) 
			{
				Mage::log('orderSave, error:'.$e->getMessage(), null, 'linksync_eparcel.log', true);
			}
		}
	}
}