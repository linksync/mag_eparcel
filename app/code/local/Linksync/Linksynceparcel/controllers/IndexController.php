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

		$isupgraded_114 = Mage::helper('linksynceparcel')->isupgraded_nonlinksync();

		if (!$isupgraded_114) {
			$table = $resource->getTableName('linksync_linksynceparcel_nonlinksync');
			$query = "ALTER TABLE `". $table ."` 
					ADD `service_type` varchar(100);";
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
	
	public function processDespatchedAction()
	{
		try 
		{
			$isManifest = false;
			$manifestNumber = false;
			$manifests = Mage::getModel('linksynceparcel/api')->getManifest();
			$xml = simplexml_load_string($manifests);
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
					if($numberOfConsignments > 0)
					{
						Mage::helper('linksynceparcel')->updateManifest($manifestNumber,$numberOfArticles,$numberOfConsignments);
						$isManifest = true;
					}
				}
			}
			
			if(!$isManifest)
			{
				echo '<li class="error-msg"><ul><li><span>No consignments are available in the current manifest</span></li></ul></li>';
				exit;
			}
			
			$notDespatchedConsignmentNumbers = Mage::helper('linksynceparcel')->getNotDespatchedAssignedConsignmentNumbers();
			if(count($notDespatchedConsignmentNumbers) == 0)
			{
				echo '<li class="error-msg"><ul><li><span>No consignments are available in the current manifest</span></li></ul></li>';
				exit;
			} 
			else 
			{
				try 
				{
					$despatch = true;
					$readyToBeDespatchedConsignments = array();
					
					$error_msg = '';
					foreach ($notDespatchedConsignmentNumbers as $consignmentNumber) 
					{
						$consignmentNumber = trim($consignmentNumber);
						$consignment = Mage::helper('linksynceparcel')->getConsignmentSingle($consignmentNumber);
						if(!$consignment)
						{
							Mage::getModel('linksynceparcel/api')->deleteConsignment($consignmentNumber);
							continue;
							$despatch = false;
							$error = Mage::helper('linksynceparcel')->__('Consignment #%s: not in the current DB', $consignmentNumber);
							$error_msg .= '<li><span>'. $error .'</span></li>';
						}
						else if(!$consignment['is_label_printed'])
						{
							$despatch = false;
							$error = Mage::helper('linksynceparcel')->__('Consignment #%s: you have not printed labels for this consignment.', $consignmentNumber);
							$error_msg .= '<li><span>'. $error .'</span></li>';
						}
						else if($consignment['print_return_labels'] && !$consignment['is_return_label_printed'])
						{
							$despatch = false;
							$error = Mage::helper('linksynceparcel')->__('Consignment #%s: you have not printed return labels for this consignment.', $consignmentNumber);
							$error_msg .= '<li><span>'. $error .'</span></li>';
						}
						Mage::helper('linksynceparcel')->updateConsignmentSingle($consignmentNumber, $consignment['order_id']);
						
						$readyToBeDespatchedConsignments[] = $consignmentNumber;
					}
					
					if($despatch)
					{
						try 
						{
							$status = Mage::getModel('linksynceparcel/api')->despatchManifest();
							$status = trim(strtolower($status));
							if($status == 'ok')
							{
								$timestamp = time();//Mage::getModel('core/date')->timestamp(time());
								$date = date('Y-m-d H:i:s', $timestamp);
								Mage::helper('linksynceparcel')->updateManifestTable($currentManifest,'despatch_date',$date);
								Mage::helper('linksynceparcel')->updateConsignmentTableByManifest($currentManifest,'despatched',1);
								Mage::helper('linksynceparcel')->updateConsignmentTableByManifest($currentManifest,'is_next_manifest',0);
								
								$changeState = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/change_order_status');
								if(!empty($changeState))
								{
									$ordersList = Mage::helper('linksynceparcel')->getOrdersByManifest($currentManifest);
									foreach($ordersList as $_order_id)
									{
										$_order = Mage::getModel('sales/order')->load($_order_id);
										$currentState = $_order->getState();
										$currentStatus = $_order->getStatus();
										if($currentState != $changeState)
										{
											if($changeState == 'complete')
											{
												//add invoice
												$this->createInvoice($_order);
											}
											
											//add shipment
											$this->createShipment($_order);
											//add ship info
											$this->addShipInfo($_order,$readyToBeDespatchedConsignments);
	
											$_order->setStatus($changeState);
											$_order->save();
										}
										else
										{
											//add shipment
											$this->createShipment($_order);
											//add ship info
											$this->addShipInfo($_order,$readyToBeDespatchedConsignments);
										}
									}
								}
								
								$final_msgs = '';
								
								$success = Mage::helper('linksynceparcel')->__('Despatching manifest is successful');
								$final_msgs .= '<li class="success-msg"><ul><li><span>'. $success .'</span></li></ul></li>';
									
								$labelContent = Mage::getModel('linksynceparcel/api')->printManifest($currentManifest);

								if($labelContent)
								{
									$filename = $currentManifest.'.pdf';
									$filepath = Mage::getBaseDir().DS.'media'.DS.'linksync'.DS.'label'.DS.'manifest'.DS.$filename;
									$handle = fopen($filepath,'wb');
									fwrite($handle, $labelContent);
									fclose($handle);
					
									Mage::helper('linksynceparcel')->updateManifestTable($currentManifest,'label',$filename);
									
									$labelLink = Mage::helper('linksynceparcel')->getManifestLabelUrl();
									$success = Mage::helper('linksynceparcel')->__('Your Manifest Summary has been generated. <a href="%s" target="_blank" style="color:blue; font-weight:bold; font-size:14px; text-decoration:underline">Please click here to view it.</a>', $labelLink.$filename.'?'.time());
									$final_msgs .= '<li class="success-msg"><ul><li><span>'. $success .'</span></li></ul></li>';
								}
								else
								{
									$error = Mage::helper('linksynceparcel')->__('Manifest label content is empty');
									$final_msgs .= '<li class="error-msg"><ul><li><span>'. $error .'</span></li></ul></li>';
								}
								
								$notify_customer = Mage::getStoreConfig('carriers/linksynceparcel/notify_customers');
								if($notify_customer) {
									Mage::helper('linksynceparcel')->notifyCustomers($currentManifest);
								}
								
								echo $final_msgs;
								exit;
							}
							else
							{
								$error = Mage::helper('linksynceparcel')->__('Despatching manifest is failed');
								echo '<li class="error-msg"><ul><li><span>'. $error .'</span></li></ul></li>';
								exit;
							}
						}
						catch (Exception $e) 
						{
							$error = Mage::helper('linksynceparcel')->__('Despatching manifest, Error: %s', $e->getMessage());
							echo '<li class="error-msg"><ul><li><span>'. $error .'</span></li></ul></li>';
							exit;
						}
					} else {
						echo '<li class="error-msg"><ul>'. $error_msg .'</ul></li>';
						exit;
					}
				} 
				catch (Exception $e) 
				{
					echo '<li class="error-msg"><ul><li><span>'. $e->getMessage() .'</span></li></ul></li>';
					exit;
				}
			}
		}
		catch (Exception $e) 
		{
			Mage::log('Despatch Failed Exception: '.$e->getMessage(), null, 'linksync_eparcel.log', true);
			echo '<li class="error-msg"><ul><li><span>'. $e->getMessage() .'</span></li></ul></li>';
			exit;
		}
	}
	
	public function createInvoice($order)
	{
		if (!$order->hasInvoices())
		{
			Mage::log('Order Id:'.$order->getId().', Invoice Create Starts', null, 'linksync_eparcel.log', true);
			try 
			{
				$invoice = $order->prepareInvoice()
				   ->setTransactionId($order->getId())
				   ->addComment($this->__('Invoice created automatically by linksync eparcel despatch.'))
				   ->register()
				   ->pay();
		
				$transaction_save = Mage::getModel('core/resource_transaction')
											->addObject($invoice)
											->addObject($invoice->getOrder());
				$transaction_save->save();
			}
			catch (Mage_Core_Exception $e) 
			{
				Mage::log('Order Id:'.$order->getId().', Invoice Failed on Despatch: '.$e->getMessage(), null, 'linksync_eparcel.log', true);
			}
		}
		else
		{
			Mage::log('Order Id:'.$order->getId().', Has Invoice Already', null, 'linksync_eparcel.log', true);
		}
	}
	
	public function createShipment($order)
	{
		if (!$order->hasShipments())
		{
			Mage::log('Order Id:'.$order->getId().', Shipment Create Starts', null, 'linksync_eparcel.log', true);
			$qty=array();
			foreach($order->getAllItems() as $eachOrderItem)
			{
				$Itemqty=0;
				$Itemqty = $eachOrderItem->getQtyOrdered()
						- $eachOrderItem->getQtyShipped()
						- $eachOrderItem->getQtyRefunded()
						- $eachOrderItem->getQtyCanceled();
				$qty[$eachOrderItem->getId()]=$Itemqty;
			}
	
			$email=true;
			$includeComment=true;
			$comment = "Shipment created automatically by linksync eparcel despatch.";
			 
			if ($order->canShip()) 
			{
				$shipment = $order->prepareShipment($qty);
				if ($shipment) 
				{
					$shipment->register();
					$shipment->addComment($comment, $email && $includeComment);
					$shipment->getOrder()->setIsInProcess(true);
					try 
					{
						$transactionSave = Mage::getModel('core/resource_transaction')
							->addObject($shipment)
							->addObject($shipment->getOrder())
							->save();
						$shipment->sendEmail($email, ($includeComment ? $comment : ''));
						Mage::log('Order Id:'.$order->getId().', Shipment Successful', null, 'linksync_eparcel.log', true);
					} 
					catch (Mage_Core_Exception $e) 
					{
						Mage::log('Order Id:'.$order->getId().', Shipment Failed: '.$e->getMessage(), null, 'linksync_eparcel.log', true);
					}
				}
				else
				{
					Mage::log('Order Id:'.$order->getId().', Prepare Shipment Failed', null, 'linksync_eparcel.log', true);
				}
			}
			else
			{
				Mage::log('Order Id:'.$order->getId().', Can not be Shipped', null, 'linksync_eparcel.log', true);
			}
		}
		else
		{
			Mage::log('Order Id:'.$order->getId().', Has Shipment Already', null, 'linksync_eparcel.log', true);
		}
	}
	
	public function addShipInfo($order,$despatchedConsignments)
	{
		$orderDespatchedConsignments = array();
		$orderConsignments = Mage::helper('linksynceparcel')->getConsignments($order->getId());
		if($orderConsignments && count($orderConsignments) > 0)
		{
			foreach($orderConsignments as $consignment)
			{
				$consignment_number = $consignment['consignment_number'];
				if(in_array($consignment_number,$despatchedConsignments))
				{
					$orderDespatchedConsignments[] = $consignment_number;
				}
			}
		}
		
		try 
		{
			$shipment_collection = Mage::getResourceModel('sales/order_shipment_collection');
			$shipment_collection->addAttributeToFilter('order_id', $order->getId());
			
			foreach($shipment_collection as $sc) 
			{
				if($orderDespatchedConsignments && count($orderDespatchedConsignments) > 0)
				{
					foreach($orderDespatchedConsignments as $consignment_number)
					{
						$shipment = Mage::getModel('sales/order_shipment');
						$shipment->load($sc->getId());
						if($shipment->getId() != '') 
						{ 
							$track = Mage::getModel('sales/order_shipment_track')
								 ->setShipment($shipment)
								 ->setData('title', Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/title'))
								 ->setData('number', $consignment_number)
								 ->setData('carrier_code', 'linksynceparcel')
								 ->setData('order_id', $shipment->getData('order_id'))
								 ->save();
						}
					}
				}
			}
		} 
		catch (Mage_Core_Exception $e) 
		{
			Mage::log('Order Id:'.$order->getId().', Add Ship Info Failed on Despatch: '.$e->getMessage(), null, 'linksync_eparcel.log', true);
		}
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