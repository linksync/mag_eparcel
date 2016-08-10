<?php

class Linksync_Linksynceparcel_Adminhtml_ConsignmentController extends Mage_Adminhtml_Controller_Action 
{
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('linksynceparcel/consignment');
	}
	
    public function indexAction() 
	{
        $this->loadLayout();
        $this->_setActiveMenu('linksync/linksynceparcel/consignment');
		$this->getLayout()->getBlock('head')->setTitle($this->__('eParcel Consignment View'));
        $this->renderLayout();
    }
	
	public function massAssignConsignmentAction() 
	{
        $ids = $this->getRequest()->getParam('order_consignment');

        if (!is_array($ids)) 
		{
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } 
		else 
		{
            try 
			{
				$success = 0;
				$consignmentNumbers = array();
                foreach ($ids as $id) 
				{
					$values = explode('_',$id);
					$orderId = (int)($values[0]);
					$consignmentNumber = $values[1];
					$order = Mage::getModel('sales/order')->load($orderId);
					$incrementId = $order->getIncrementId();
					if($consignmentNumber == '0')
					{
						$error = Mage::helper('linksynceparcel')->__('Order #%s: does not have consignment', $incrementId);
						Mage::getSingleton('adminhtml/session')->addError($error);
					}
					else
					{
						try 
						{
							$status = Mage::getModel('linksynceparcel/api')->assignConsignmentToManifest($consignmentNumber);
							$status = trim(strtolower($status));
							if($status == 'ok')
							{
								$success++;
								$consignmentNumbers[] = $consignmentNumber;
								Mage::helper('linksynceparcel')->updateConsignmentTable($orderId,$consignmentNumber,'is_next_manifest', 1);
								$successmsg = Mage::helper('linksynceparcel')->__('Consignment #%s: successfully assigned', $consignmentNumber);
								Mage::getSingleton('adminhtml/session')->addSuccess($successmsg);
							}
							else
							{
								$error = Mage::helper('linksynceparcel')->__('Consignment #%s: failed to assign', $consignmentNumber);
								Mage::getSingleton('adminhtml/session')->addError($error);
							}
						}
						catch (Exception $e) 
						{
							$error = Mage::helper('linksynceparcel')->__('Consignment #%s, Error: %s', $consignmentNumber, $e->getMessage());
							Mage::getSingleton('adminhtml/session')->addError($error);
						}
					}
					
                }
				
				if($orderId > 0 && $success > 0)
				{
					$manifestNumber = Mage::helper('linksynceparcel')->getManifestNumber();
					if($manifestNumber)
					{
						foreach($consignmentNumbers as $consignmentNumber)
						{
							Mage::helper('linksynceparcel')->updateConsignmentTable2($consignmentNumber,'manifest_number', $manifestNumber);
						}
					}
				}
            } 
			catch (Exception $e) 
			{
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
		
        $this->_redirect('*/*/index');
    }
	
	public function massUnassignConsignmentAction() 
	{
        $ids = $this->getRequest()->getParam('order_consignment');

        if (!is_array($ids)) 
		{
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } 
		else 
		{
            try 
			{
				$success = 0;
                foreach ($ids as $id) 
				{
					$values = explode('_',$id);
					$orderId = (int)($values[0]);
					$consignmentNumber = $values[1];
					$order = Mage::getModel('sales/order')->load($orderId);
					$incrementId = $order->getIncrementId();
					if($consignmentNumber == '0')
					{
						$error = Mage::helper('linksynceparcel')->__('Order #%s: does not have consignment', $incrementId);
						Mage::getSingleton('adminhtml/session')->addError($error);
					}
					else
					{
						try 
						{
							$status = Mage::getModel('linksynceparcel/api')->unAssignConsignment($consignmentNumber);
							$status = trim(strtolower($status));
							if($status == 'ok')
							{
								$success++;
								Mage::helper('linksynceparcel')->updateConsignmentTable($orderId,$consignmentNumber,'manifest_number', '');
								Mage::helper('linksynceparcel')->updateConsignmentTable($orderId,$consignmentNumber,'is_next_manifest', 0);
								$successmsg = Mage::helper('linksynceparcel')->__('Consignment #%s: successfully unassigned', $consignmentNumber);
								Mage::getSingleton('adminhtml/session')->addSuccess($successmsg);
							}
							else
							{
								$error = Mage::helper('linksynceparcel')->__('Consignment #%s: failed to unassign',$consignmentNumber);
								Mage::getSingleton('adminhtml/session')->addError($error);
							}
						}
						catch (Exception $e) 
						{
							$error = Mage::helper('linksynceparcel')->__('Consignment #%s, Error: %s', $consignmentNumber, $e->getMessage());
							Mage::getSingleton('adminhtml/session')->addError($error);
						}
					}
					
                }
				
				if($success > 0)
				{
					Mage::helper('linksynceparcel')->getManifestNumber();
					Mage::helper('linksynceparcel')->deleteManifest();
				}
            } 
			catch (Exception $e) 
			{
				Mage::helper('linksynceparcel')->deleteManifest();
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
	public function massGenerateLabelsAction() 
	{
        $ids = $this->getRequest()->getParam('order_consignment');
		if (!is_array($ids))
		{
			if(!empty($ids))
			{
				$ids = array($ids);
			}
		}

        if (!is_array($ids)) 
		{
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } 
		else 
		{
            try 
			{
				$sameGroup = true;
				$isExpressCode = false;
				$isStandardCode = false;
				$isInternational = false;
				
				foreach ($ids as $id) 
				{
					$values = explode('_',$id);
					$orderId = (int)($values[0]);
					
					$chargeCode = Mage::helper('linksynceparcel')->getOrderChargeCode($orderId);
					$chargeCodes = Mage::helper('linksynceparcel')->getChargeCodes();
					$chargeCodeData = $chargeCodes[$chargeCode];
					if($chargeCodeData['serviceType'] == 'express')
						$isExpressCode = true;
					if($chargeCodeData['serviceType'] == 'standard')
						$isStandardCode = true;
					if($chargeCodeData['serviceType'] == 'international')
						$isInternational = true;
				}
				
				$valid = true;
				if($isExpressCode && $isStandardCode) {
					$valid = false;
				}
				if($isExpressCode && $isInternational) {
					$valid = false;
				}
				if($isStandardCode && $isInternational) {
					$valid = false;
				}
				
				if($valid) {
					$consignmentNumbers = array();
					$chargeCodes = array();
					foreach ($ids as $id) 
					{
						$values = explode('_',$id);
						$orderId = (int)($values[0]);
						$consignmentNumber = $values[1];
						$order = Mage::getModel('sales/order')->load($orderId);
						$incrementId = $order->getIncrementId();
						if($consignmentNumber != '0')
						{
							$consignmentNumbers[] = $consignmentNumber;
							
							$chargeCode = Mage::helper('linksynceparcel')->getOrderChargeCode($orderId,$consignmentNumber);
							$chargeCodes[] = $chargeCode;
							$labelContent = Mage::getModel('linksynceparcel/api')->getLabelsByConsignments($consignmentNumber,$chargeCode);
							if($labelContent)
							{
								$filename = $consignmentNumber.'.pdf';
								$filepath = Mage::getBaseDir().DS.'media'.DS.'linksync'.DS.'label'.DS.'consignment'.DS.$filename;
								$handle = fopen($filepath,'wb');
								fwrite($handle, $labelContent);
								fclose($handle);
	
								Mage::helper('linksynceparcel')->updateConsignmentTable($orderId,$consignmentNumber,'label', $filename);
								Mage::helper('linksynceparcel')->updateConsignmentTable($orderId,$consignmentNumber,'is_label_created', 1);
								Mage::helper('linksynceparcel')->updateConsignmentTable($orderId,$consignmentNumber,'is_label_printed', 1);
							}
						}
					}
					
					if(count($consignmentNumbers) > 0)
					{
						$labelContent = Mage::getModel('linksynceparcel/api')->getLabelsByConsignments(implode(',',$consignmentNumbers),$chargeCodes[0]);
						if($labelContent)
						{
							$filename = 'bulk-consignments-label.pdf';
							$filepath = Mage::getBaseDir().DS.'media'.DS.'linksync'.DS.'label'.DS.'consignment'.DS.$filename;
							$handle = fopen($filepath,'wb');
							fwrite($handle, $labelContent);
							fclose($handle);
							$labelLink = Mage::helper('linksynceparcel')->getConsignmentLabelUrl();
							$success = Mage::helper('linksynceparcel')->__('Label is generated. <a href="%s" target="_blank" style="color:blue; font-weight:bold; font-size:14px; text-decoration:underline">Please click here to view it.</a>',$labelLink.$filename.'?'.time());
							Mage::getSingleton('adminhtml/session')->addSuccess($success);
							/*Mage::app()->getFrontController()->getResponse()->setRedirect($labelLink.$filename)->sendResponse();
							$this->_redirectUrl($labelLink.$filename);
							exit;*/
						}
						else
						{
							$error = Mage::helper('linksynceparcel')->__('Failed to generate label');
							Mage::getSingleton('adminhtml/session')->addError($error);
						}
					}
					else
					{
						$error = Mage::helper('linksynceparcel')->__('None of the selected items have consignments');
						Mage::getSingleton('adminhtml/session')->addError($error);
					}
				} else {
					$error = Mage::helper('linksynceparcel')->__('You can only print multiple consignment labels for the same Delivery Type - they must be all Express Post or all eParcel Standard.');
					Mage::getSingleton('adminhtml/session')->addError($error);
				}
            } 
			catch (Exception $e) 
			{
                Mage::getSingleton('adminhtml/session')->addError(ucfirst($e->getMessage()));
            }
        }
        $this->_redirect('*/*/index');
    }

	public function massGenerateReturnLabelsAction() 
	{
        $ids = $this->getRequest()->getParam('order_consignment');
		if (!is_array($ids))
		{
			if(!empty($ids))
			{
				$ids = array($ids);
			}
		}

        if (!is_array($ids)) 
		{
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } 
		else 
		{
            try 
			{
				$sameGroup = true;
				$isExpressCode = false;
				$isStandardCode = false;
				
				foreach ($ids as $id) 
				{
					$values = explode('_',$id);
					$orderId = (int)($values[0]);
					
					$chargeCode = Mage::helper('linksynceparcel')->getOrderChargeCode($orderId);
					if(!$isExpressCode && Mage::helper('linksynceparcel')->isExpressPostCode($chargeCode))
						$isExpressCode = true;
					if(!$isStandardCode && Mage::helper('linksynceparcel')->isLinksynceparcelStandardCode($chargeCode))
						$isStandardCode = true;
				}
				
				if (!($isExpressCode && $isStandardCode))
				{
					$consignmentNumbers = array();
					foreach ($ids as $id) 
					{
						$values = explode('_',$id);
						$orderId = (int)($values[0]);
						$consignmentNumber = $values[1];
						$order = Mage::getModel('sales/order')->load($orderId);
						$incrementId = $order->getIncrementId();
						if($consignmentNumber != '0')
						{
							$consignmentNumbers[] = $consignmentNumber;
							
							$labelContent = Mage::getModel('linksynceparcel/api')->getReturnLabelsByConsignments($consignmentNumber);
							if($labelContent)
							{
								$filename = $consignmentNumber.'.pdf';
								$filepath = Mage::getBaseDir().DS.'media'.DS.'linksync'.DS.'label'.DS.'returnlabels'.DS.$filename;
								$handle = fopen($filepath,'wb');
								fwrite($handle, $labelContent);
								fclose($handle);
								Mage::helper('linksynceparcel')->updateConsignmentTable($orderId,$consignmentNumber,'is_return_label_printed', 1);
							}
						}
					}
					
					if(count($consignmentNumbers) > 0)
					{
						$labelContent = Mage::getModel('linksynceparcel/api')->getReturnLabelsByConsignments(implode(',',$consignmentNumbers));
						if($labelContent)
						{
							$filename = 'bulk-consignments-return-label.pdf';
							$filepath = Mage::getBaseDir().DS.'media'.DS.'linksync'.DS.'label'.DS.'returnlabels'.DS.$filename;
							$handle = fopen($filepath,'wb');
							fwrite($handle, $labelContent);
							fclose($handle);
							$labelLink = Mage::helper('linksynceparcel')->getConsignmentReturnLabelUrl();
							$success = Mage::helper('linksynceparcel')->__('Return Label is generated. <a href="%s" target="_blank" style="color:blue; font-weight:bold; font-size:14px; text-decoration:underline">Please click here to view it.</a>',$labelLink.$filename.'?'.time());
							Mage::getSingleton('adminhtml/session')->addSuccess($success);
						}
						else
						{
							$error = Mage::helper('linksynceparcel')->__('Failed to generate label');
							Mage::getSingleton('adminhtml/session')->addError($error);
						}
					}
					else
					{
						$error = Mage::helper('linksynceparcel')->__('None of the selected items have consignments');
						Mage::getSingleton('adminhtml/session')->addError($error);
					}
				}
				else
				{
					$error = Mage::helper('linksynceparcel')->__('You can only print multiple consignment labels for the same Delivery Type - they must be all Express Post or all eParcel Standard.');
					Mage::getSingleton('adminhtml/session')->addError($error);
				}
            } 
			catch (Exception $e) 
			{
                Mage::getSingleton('adminhtml/session')->addError(ucfirst($e->getMessage()));
            }
        }
        $this->_redirect('*/*/index');
    }
	
	public function massDeleteConsignmentAction() 
	{
        $ids = $this->getRequest()->getParam('order_consignment');

        if (!is_array($ids)) 
		{
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } 
		else 
		{
            try 
			{
				$success = 0;
                foreach ($ids as $id) 
				{
					$values = explode('_',$id);
					$orderId = (int)($values[0]);
					$consignmentNumber = $values[1];
					$order = Mage::getModel('sales/order')->load($orderId);
					$incrementId = $order->getIncrementId();
					if($consignmentNumber == '0')
					{
						$error = Mage::helper('linksynceparcel')->__('Order #%s: does not have consignment', $incrementId);
						Mage::getSingleton('adminhtml/session')->addError($error);
					}
					else
					{
						try 
						{
							$status = Mage::getModel('linksynceparcel/api')->deleteConsignment($consignmentNumber);
							$status = trim(strtolower($status));
							if($status == 'ok')
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
								
								Mage::helper('linksynceparcel')->deleteConsignment($orderId,$consignmentNumber);
								
								$success++;
								$successmsg = Mage::helper('linksynceparcel')->__('Consignment #%s: successfully deleted', $consignmentNumber);
								Mage::getSingleton('adminhtml/session')->addSuccess($successmsg);
							}
							else
							{
								$error = Mage::helper('linksynceparcel')->__('Consignment #%s: failed to delete', $consignmentNumber);
								Mage::getSingleton('adminhtml/session')->addError($error);
							}
						}
						catch (Exception $e) 
						{
							$error = Mage::helper('linksynceparcel')->__('Consignment #%s, Error: %s', $consignmentNumber, $e->getMessage());
							Mage::getSingleton('adminhtml/session')->addError($error);
						}
					}
                }
				
				if($success > 0)
				{
					$manifestNumber = Mage::helper('linksynceparcel')->getManifestNumber();
					Mage::helper('linksynceparcel')->deleteManifest();
				}
            } 
			catch (Exception $e) 
			{
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
	public function massCreateConsignmentAction() 
	{
        $ids = $this->getRequest()->getParam('order_consignment');

        if (!is_array($ids)) 
		{
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } 
		else 
		{
            try 
			{
				$success = 0;
				
				$data = $this->getRequest()->getParams();
				$data['partial_delivery_allowed'] = Mage::getStoreConfig('carriers/linksynceparcel/partial_delivery_allowed');
				if($data['partial_delivery_allowed'] == 2)
				{
					$data['partial_delivery_allowed'] = 0;
				}
				$data['delivery_signature_allowed'] = Mage::getStoreConfig('carriers/linksynceparcel/signature_required');
				if($data['delivery_signature_allowed'] == 2)
				{
					$data['delivery_signature_allowed'] = 0;
				}
				$data['transit_cover_required'] = Mage::getStoreConfig('carriers/linksynceparcel/insurance');
				if($data['transit_cover_required'] == 2)
				{
					$data['transit_cover_required'] = 0;
				}
				$data['print_return_labels'] = Mage::getStoreConfig('carriers/linksynceparcel/print_return_labels');
				if($data['print_return_labels'] == 2)
				{
					$data['print_return_labels'] = 0;
				}
				$data['email_notification'] = Mage::getStoreConfig('carriers/linksynceparcel/post_email_notification');
				if($data['email_notification'] == 2)
				{
					$data['email_notification'] = 0;
				}
				
				//Include defaults option
				$data['delivery_instruction'] = '';
				$data['safe_drop'] = Mage::getStoreConfig('carriers/linksynceparcel/safe_drop');
				$data['transit_cover_amount'] = Mage::getStoreConfig('carriers/linksynceparcel/default_insurance_value');
				$data['insurance'] = Mage::getStoreConfig('carriers/linksynceparcel/int_insurance');
				$data['order_value_insurance'] = Mage::getStoreConfig('carriers/linksynceparcel/order_value_insured_value');
				$data['insurance_value'] = Mage::getStoreConfig('carriers/linksynceparcel/default_int_insurance_value');
				$data['export_declaration_number'] = '';
				$data['has_commercial_value'] = Mage::getStoreConfig('carriers/linksynceparcel/has_commercial_value');
				$data['product_classification'] = Mage::getStoreConfig('carriers/linksynceparcel/default_product_classification');
				if($data['has_commercial_value'] == 1) {
					$data['product_classification'] = 991;
				}
				$data['product_classification_text'] = '';
				if($data['product_classification']==991){
					$data['product_classification_text'] = Mage::getStoreConfig('carriers/linksynceparcel/classification_explanation');
				}
				$data['country_origin'] = Mage::getStoreConfig('carriers/linksynceparcel/default_country_origin');
				$data['hs_tariff'] = Mage::getStoreConfig('carriers/linksynceparcel/default_has_tariff');
				$data['contents'] = Mage::getStoreConfig('carriers/linksynceparcel/default_contents');
				$data['contains_dangerous_goods'] = 0;
				$data['declared_value'] = Mage::getStoreConfig('carriers/linksynceparcel/order_value_declared_value');
				$data['maximum_declared_value'] = Mage::getStoreConfig('carriers/linksynceparcel/maximum_declared_value');
				$data['fixed_declared_value'] = Mage::getStoreConfig('carriers/linksynceparcel/fixed_declared_value');
				
                foreach ($ids as $id) 
				{
					$values = explode('_',$id);
					$orderId = (int)($values[0]);
					$order = Mage::getModel('sales/order')->load($orderId);
					$incrementId = $order->getIncrementId();
					$address = $order->getShippingAddress();
					$country = $address->getCountry();
					if(Mage::getStoreConfig('carriers/linksynceparcel/copy_order_notes') == 1) {
						$ordernotes = Mage::helper('linksynceparcel')->getNotes($order);
						$data['delivery_instruction'] = $ordernotes;
					}
					$validateInt = Mage::helper('linksynceparcel')->validateInternationalConsignment($data, $order, $country);
					if($country != 'AU' && $validateInt != false) {
						$errors = implode('<br>', $validateInt);
						Mage::getSingleton('adminhtml/session')->addError($errors);
					} else {
						if(!$order->getIsAddressValid() && $country == 'AU')
						{
							$error = Mage::helper('linksynceparcel')->__('Order #%s: Please validate the address before creating consignment', $incrementId);
							Mage::getSingleton('adminhtml/session')->addError($error);
						}
						else
						{
							try 
							{
								
								if($data['partial_delivery_allowed'])
								{
									if(Mage::helper('linksynceparcel')->isDisablePartialDeliveryMethod($order->getId()))
									{
										$data['partial_delivery_allowed'] = 0;
									}
								}
								
								$articleData = Mage::helper('linksynceparcel')->prepareArticleDataBulk($data, $order);
								$content = $articleData['content'];
								$chargeCode = $articleData['charge_code'];
								$total_weight = $articleData['total_weight'];
								$consignmentData = Mage::getModel('linksynceparcel/api')->createConsignment($content,0,$chargeCode);
								if($consignmentData)
								{
									$consignmentNumber = $consignmentData->consignmentNumber;
									$manifestNumber = $consignmentData->manifestNumber;
									
									Mage::helper('linksynceparcel')->insertConsignment($orderId,$consignmentNumber,$data,$manifestNumber,$chargeCode,$total_weight,$country);
									Mage::helper('linksynceparcel')->updateArticles($orderId,$consignmentNumber,$consignmentData->articles,$data,$content);
									Mage::helper('linksynceparcel')->insertManifest($manifestNumber);
							
									$labelContent = $consignmentData->lpsLabels->labels->label;
									Mage::helper('linksynceparcel')->generateDocument($consignmentNumber,$labelContent,'label');

									$successmsg = Mage::helper('linksynceparcel')->__('Order #%s: Consignment #%s created successfully', $incrementId,$consignmentNumber);
									Mage::getSingleton('adminhtml/session')->addSuccess($successmsg);
								}
								else
								{
									$error = Mage::helper('linksynceparcel')->__('Order #%s: Failed to create consignment',$incrementId);
									Mage::getSingleton('adminhtml/session')->addError($error);
								}
							}
							catch (Exception $e) 
							{
								$error = Mage::helper('linksynceparcel')->__('Order #%s, Error: %s', $incrementId, $e->getMessage());
								Mage::getSingleton('adminhtml/session')->addError($error);
							}
						}
					}
                }
            } 
			catch (Exception $e) 
			{
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
	public function despatchAction() 
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
				throw new Exception(Mage::helper('linksynceparcel')->__('No consignments are available in the current manifest'));
			}
			
			$notDespatchedConsignmentNumbers = Mage::helper('linksynceparcel')->getNotDespatchedAssignedConsignmentNumbers();
			if(count($notDespatchedConsignmentNumbers) == 0)
			{
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('linksynceparcel')->__('No consignments are available in the current manifest'));
			} 
			else 
			{
				try 
				{
					$despatch = true;
					$readyToBeDespatchedConsignments = array();
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
							Mage::getSingleton('adminhtml/session')->addError($error);
						}
						else if(!$consignment['is_label_printed'])
						{
							$despatch = false;
							$error = Mage::helper('linksynceparcel')->__('Consignment #%s: you have not printed labels for this consignment.', $consignmentNumber);
							Mage::getSingleton('adminhtml/session')->addError($error);
						}
						else if($consignment['print_return_labels'] && !$consignment['is_return_label_printed'])
						{
							$despatch = false;
							$error = Mage::helper('linksynceparcel')->__('Consignment #%s: you have not printed return labels for this consignment.', $consignmentNumber);
							Mage::getSingleton('adminhtml/session')->addError($error);
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
								
								$success = Mage::helper('linksynceparcel')->__('Despatching manifest is successful');
								Mage::getSingleton('adminhtml/session')->addSuccess($success);
									
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
									Mage::getSingleton('adminhtml/session')->addSuccess($success);
								}
								else
								{
									$error = Mage::helper('linksynceparcel')->__('Manifest label content is empty');
									Mage::getSingleton('adminhtml/session')->addError($error);
								}
								
								$notify_customer = Mage::getStoreConfig('carriers/linksynceparcel/notify_customers');
								if($notify_customer) {
									Mage::helper('linksynceparcel')->notifyCustomers($currentManifest);
								}
							}
							else
							{
								$error = Mage::helper('linksynceparcel')->__('Despatching manifest is failed');
								Mage::getSingleton('adminhtml/session')->addError($error);
							}
						}
						catch (Exception $e) 
						{
							$error = Mage::helper('linksynceparcel')->__('Despatching manifest, Error: %s', $e->getMessage());
							Mage::getSingleton('adminhtml/session')->addError($error);
						}
					}
				} 
				catch (Exception $e) 
				{
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				}
			}
		}
		catch (Exception $e) 
		{
			Mage::log('Despatch Failed Exception: '.$e->getMessage(), null, 'linksync_eparcel.log', true);
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
        $this->_redirect('*/*/index');
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
	
	public function getreturnlabelsAction() 
	{
		try 
		{
			$labelContent = Mage::getModel('linksynceparcel/api')->getReturnLabels();
			if($labelContent)
			{
				$filename = 'return-labels.pdf';
				$filepath = Mage::getBaseDir().DS.'media'.DS.'linksync'.DS.'label'.DS.'consignment'.DS.$filename;
				$handle = fopen($filepath,'wb');
				fwrite($handle, $labelContent);
				fclose($handle);
				$labelLink = Mage::helper('linksynceparcel')->getConsignmentLabelUrl();
				$success = Mage::helper('linksynceparcel')->__('Return label is generated, please <a href="%s" target="_blank" style="color:blue; font-weight:bold; font-size:14px; text-decoration:underline">click</a> to view the label.', $labelLink.$filename.'?'.time());
				Mage::getSingleton('adminhtml/session')->addSuccess($success);
			}
			else
			{
				$error = Mage::helper('linksynceparcel')->__('Failed to get return labels');
				Mage::getSingleton('adminhtml/session')->addError($error);
			}
		}
		catch (Exception $e) 
		{
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
        $this->_redirect('*/*/index');
    }
	
	public function massMarkDespatchedAction() 
	{
        $ids = $this->getRequest()->getParam('order_consignment');

        if (!is_array($ids)) 
		{
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } 
		else 
		{
            try 
			{
				$success = 0;
                foreach ($ids as $id) 
				{
					$values = explode('_',$id);
					$orderId = (int)($values[0]);
					$consignmentNumber = $values[1];
					$order = Mage::getModel('sales/order')->load($orderId);
					$incrementId = $order->getIncrementId();
					if($consignmentNumber == '0')
					{
						$error = Mage::helper('linksynceparcel')->__('Order #%s: does not have consignment', $incrementId);
						Mage::getSingleton('adminhtml/session')->addError($error);
					}
					else
					{
						try 
						{
							Mage::helper('linksynceparcel')->updateConsignmentTable($orderId,$consignmentNumber,'despatched', 1);
							
							$changeState = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/change_order_status');
							if(!empty($changeState))
							{
								$currentState = $order->getStatus();
								if($currentState != $changeState)
								{
									$order->setStatus($changeState);
									$order->save();
								}
							}
							
							$successmsg = Mage::helper('linksynceparcel')->__('Consignment #%s: successfully marked as despatched', $consignmentNumber);
							Mage::getSingleton('adminhtml/session')->addSuccess($successmsg);
						}
						catch (Exception $e) 
						{
							$error = Mage::helper('linksynceparcel')->__('Consignment #%s, Error: %s', $consignmentNumber, $e->getMessage());
							Mage::getSingleton('adminhtml/session')->addError($error);
						}
					}
                }

            } 
			catch (Exception $e) 
			{
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}