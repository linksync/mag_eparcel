<?php
class Linksync_Linksynceparcel_ConsignmentController extends Mage_Adminhtml_Controller_Sales_Shipment
{
 	public function createAction()
    {
		if(isset($_REQUEST['number_of_articles']))
		{
			$number_of_articles = trim($this->getRequest()->getParam('number_of_articles'));
			if(empty($number_of_articles))
			{
				Mage::getSingleton('adminhtml/session')->addError('Enter number of articles');
			}
			else if(!is_numeric($number_of_articles))
			{
				Mage::getSingleton('adminhtml/session')->addError('Enter valid number of articles');
				$_POST['number_of_articles'] = '';
			}
			else if($number_of_articles < 1)
			{
				Mage::getSingleton('adminhtml/session')->addError('Enter valid number of articles');
				$_POST['number_of_articles'] = '';
			}
			else if($number_of_articles > 100)
			{
				Mage::getSingleton('adminhtml/session')->addError('Number of articles can be 1-100 per request');
				$_POST['number_of_articles'] = '';
			}
		}
		
		$this->loadLayout()
			->_setActiveMenu('sales/order')
			->renderLayout();
    }
	
	public function editArticleAction()
    {
		if(isset($_REQUEST['number_of_articles']))
		{
			$number_of_articles = trim($this->getRequest()->getParam('number_of_articles'));
			if(empty($number_of_articles))
			{
				Mage::getSingleton('adminhtml/session')->addError('Enter number of articles');
			}
			else if(!is_numeric($number_of_articles))
			{
				Mage::getSingleton('adminhtml/session')->addError('Enter valid number of articles');
			}
			else if($number_of_articles < 1)
			{
				Mage::getSingleton('adminhtml/session')->addError('Enter valid number of articles');
			}
		}
		
		$this->loadLayout()
			->_setActiveMenu('sales/order')
			->renderLayout();
    }
	
	public function editConsignmentAction()
    {
		$this->loadLayout()
			->_setActiveMenu('sales/order')
			->renderLayout();
    }
	
	public function addArticleAction()
    {
		if(isset($_REQUEST['number_of_articles']))
		{
			$number_of_articles = trim($this->getRequest()->getParam('number_of_articles'));
			if(empty($number_of_articles))
			{
				Mage::getSingleton('adminhtml/session')->addError('Enter number of articles');
			}
			else if(!is_numeric($number_of_articles))
			{
				Mage::getSingleton('adminhtml/session')->addError('Enter valid number of articles');
			}
			else if($number_of_articles < 1)
			{
				Mage::getSingleton('adminhtml/session')->addError('Enter valid number of articles');
			}
		}
		
		$this->loadLayout()
			->_setActiveMenu('sales/order')
			->renderLayout();
    }
	
	public function getOrder()
	{
		$order_id = $this->getRequest()->getParam('order_id');
		if($order_id > 0)
		{
			return Mage::getModel("sales/order")->load($order_id);
		}
	}
	
	public function getTotalItems()
	{
		$order = $this->getOrder();
		return Mage::getModel('linksynceparcel/consignment')->getTotalItems($order);
	}
	
	public function labelCreateAction()
    {
		$data = $this->getRequest()->getParams();
		$consignmentNumber = $data['consignment_number'];
		$chargecode = Mage::helper('linksynceparcel')->getOrderChargeCode($this->getOrder()->getId(), $consignmentNumber);

		try
		{
			$labelContent = Mage::getModel('linksynceparcel/api')->getLabelsByConsignments($consignmentNumber,$chargecode);

			if($labelContent)
			{
				$filename = $consignmentNumber.'.pdf';
				$filepath = Mage::getBaseDir().DS.'media'.DS.'linksync'.DS.'label'.DS.'consignment'.DS.$filename;
				$handle = fopen($filepath,'wb');
				fwrite($handle, $labelContent);
				fclose($handle);
				Mage::helper('linksynceparcel')->updateConsignmentLabel($this->getOrder()->getId(),$consignmentNumber,$filename);

				$this->_getSession()->addSuccess($this->__('The label for consignment #'.$consignmentNumber.' has been created successfully.'));
				$this->_redirect("adminhtml/sales_order/view", array('order_id' => $this->getOrder()->getId(),'active_tab' => 'linksync_eparcel'));
			}
		}
		catch(Exception $e)
		{
			;//log
			 $this->_getSession()->addError($this->__('Cannot create consignment label.').$e->getMessage());
             $this->_redirect("adminhtml/sales_order/view", array('order_id' => $this->getOrder()->getId(),'active_tab' => 'linksync_eparcel'));
		}
	}
	
	public function returnLabelCreateAction()
    {
		$data = $this->getRequest()->getParams();
		$consignmentNumber = $data['consignment_number'];

		try
		{
			$labelContent = Mage::getModel('linksynceparcel/api')->getReturnLabelsByConsignments($consignmentNumber);

			if($labelContent)
			{
				$filename = $consignmentNumber.'.pdf';
				$filepath = Mage::getBaseDir().DS.'media'.DS.'linksync'.DS.'label'.DS.'returnlabels'.DS.$filename;
				$handle = fopen($filepath,'wb');
				fwrite($handle, $labelContent);
				fclose($handle);

				$this->_getSession()->addSuccess($this->__('The return label for consignment #'.$consignmentNumber.' has been created successfully.'));
				$this->_redirect("adminhtml/sales_order/view", array('order_id' => $this->getOrder()->getId(),'active_tab' => 'linksync_eparcel'));
			}
		}
		catch(Exception $e)
		{
			;//log
			 $this->_getSession()->addError($this->__('Cannot create consignment return label.').$e->getMessage());
             $this->_redirect("adminhtml/sales_order/view", array('order_id' => $this->getOrder()->getId(),'active_tab' => 'linksync_eparcel'));
		}
	}
	
	public function deleteConsignmentAction()
    {
		$data = $this->getRequest()->getParams();
		$consignmentNumber = $data['consignment_number'];
		$consignment = Mage::helper('linksynceparcel')->getConsignment($this->getOrder()->getId(),$consignmentNumber);
		try
		{
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
					Mage::helper('linksynceparcel')->deleteConsignment($this->getOrder()->getId(),$consignmentNumber);
					Mage::helper('linksynceparcel')->deleteManifest2($consignment['manifest_number']);
	
					$this->_getSession()->addSuccess($this->__('Consignment #'.$consignmentNumber.' has been deleted successfully.'));
					$this->_redirect("adminhtml/sales_order/view", array('order_id' => $this->getOrder()->getId(),'active_tab' => 'linksync_eparcel'));
				}
		}
		catch(Exception $e)
		{
			$error = $this->__('Could not delete consignment.').$e->getMessage();
			$this->_getSession()->addError($error);
			Mage::log($error, null, 'linksync_eparcel.log', true);
			Mage::helper('linksynceparcel')->deleteManifest2($consignment['manifest_number']);
            $this->_redirect("adminhtml/sales_order/view", array('order_id' => $this->getOrder()->getId(),'active_tab' => 'linksync_eparcel'));
		}
	}
	
	public function deleteConsignmentArticleAction()
    {
		$data = $this->getRequest()->getParams();
		$consignmentNumber = $data['consignment_number'];
		$consignment = Mage::helper('linksynceparcel')->getConsignment($this->getOrder()->getId(),$consignmentNumber);
		try
		{
			$ok = Mage::getModel('linksynceparcel/api')->deleteConsignment($consignmentNumber);
			if($ok)
			{
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
				
				Mage::helper('linksynceparcel')->deleteConsignment($this->getOrder()->getId(),$consignmentNumber);
				Mage::helper('linksynceparcel')->deleteManifest2($consignment['manifest_number']);
				
				$this->_getSession()->addSuccess($this->__('Article has been deleted from consignment #'.$consignmentNumber.' successfully.'));
				$this->_getSession()->addSuccess($this->__('Consignment #'.$consignmentNumber.' has been deleted successfully.'));
				$this->_redirect("adminhtml/sales_order/view", array('order_id' => $this->getOrder()->getId(),'active_tab' => 'linksync_eparcel'));
			}
		}
		catch(Exception $e)
		{
			$error = $this->__('Could not delete consignment.').$e->getMessage();
			$this->_getSession()->addError($error);
			Mage::log($error, null, 'linksync_eparcel.log', true);
			Mage::helper('linksynceparcel')->deleteManifest2($consignment['manifest_number']);
            $this->_redirect("adminhtml/sales_order/view", array('order_id' => $this->getOrder()->getId(),'active_tab' => 'linksync_eparcel'));
		}
	}
	
	public function deleteArticleAction()
    {
		$order_id = $this->getOrder()->getId();
		$data = $this->getRequest()->getParams();
		$consignmentNumber = $data['consignment_number'];
		$articleNumber = $data['article_number'];
		
		try
		{
			$articles = Mage::helper('linksynceparcel')->getArticles($order_id, $consignmentNumber);
			if($articles && count($articles) > 1 )
			{
				$deleteArticle = Mage::helper('linksynceparcel')->deleteArticle($order_id,$consignmentNumber,$articleNumber);
				$articleData = Mage::helper('linksynceparcel')->prepareModifiedArticleData($this->getOrder(), $consignmentNumber);
				$content = $articleData['content'];
				$chargeCode = $articleData['charge_code'];
				$total_weight = $articleData['total_weight'];
				$consignmentData = Mage::getModel('linksynceparcel/api')->modifyConsignment($content,$consignmentNumber,$chargeCode);
				if($consignmentData)
				{
					$consignmentNumber = $consignmentData->consignmentNumber;
					$manifestNumber = $consignmentData->manifestNumber;
					Mage::helper('linksynceparcel')->updateConsignmentTable2($consignmentNumber,'weight', $total_weight);
					Mage::helper('linksynceparcel')->updateConsignmentTable2($consignmentNumber,'is_label_printed', 0);
					Mage::helper('linksynceparcel')->updateArticles($order_id,$consignmentNumber,$consignmentData->articles,$data,$content);
					Mage::helper('linksynceparcel')->insertManifest($manifestNumber);
					
					$labelContent = $consignmentData->lpsLabels->labels->label;
					Mage::helper('linksynceparcel')->generateDocument($consignmentNumber,$labelContent,'label');
				
					$this->_getSession()->addSuccess($this->__('Article #'.$articleNumber.' has been deleted from consignment #'.$consignmentNumber.' successfully.'));
					$this->_redirect("adminhtml/sales_order/view", array('order_id' => $order_id ,'active_tab' => 'linksync_eparcel'));
				}
			}
			else
			{
				$this->deleteConsignmentArticleAction();
			}
		}
		catch(Exception $e)
		{
			$error = $this->__('Could not delete article, Error: ').$e->getMessage();
			$this->_getSession()->addError($error);
			Mage::log($error, null, 'linksync_eparcel.log', true);
        	$this->_redirect("adminhtml/sales_order/view", array('order_id' => $order_id ,'active_tab' => 'linksync_eparcel'));
		}
	}
	
    public function saveAction()
    {
		$order_id = $this->getOrder()->getId();
		$address = $this->getOrder()->getShippingAddress();
		$country = $address->getCountry();
		if($country != 'AU') {
			$this->getRequest()->setParam('number_of_articles', 1);
		}
		$number_of_articles = (int)trim($this->getRequest()->getParam('number_of_articles'));
		$data = $this->getRequest()->getParams();
		
		$tempCanConsignments = (int)($number_of_articles/20);
		$canConsignments = $tempCanConsignments;
		$remainArticles = $number_of_articles % 20;
		
		$validate = Mage::helper('linksynceparcel')->validateInternationalConsignment($data, $this->getOrder(), $country);
		if($validate != false) {
			$errors = implode('<br>', $validate);
			$this->_getSession()->addError($errors);
		} else {

			if( $remainArticles > 0)
			{
				$canConsignments++;
			}
			
			for($i=0;$i<$canConsignments;$i++)
			{
				$data['start_index'] = ($i * 20 ) + 1;
				if( ($i+1) <= $tempCanConsignments)
				{
					$data['end_index'] = ($i * 20 ) + 20;
				}
				else
				{
					$data['end_index'] = ($i * 20 ) + $remainArticles;
				}
				
				try
				{
					$articleData = Mage::helper('linksynceparcel')->prepareArticleData($data, $this->getOrder());
					$content = $articleData['content'];
					$chargeCode = $articleData['charge_code'];
					$consignmentData = Mage::getModel('linksynceparcel/api')->createConsignment($content,0,$chargeCode);
					$total_weight = $articleData['total_weight'];
					if($consignmentData)
					{
						$consignmentNumber = $consignmentData->consignmentNumber;
						$manifestNumber = $consignmentData->manifestNumber;
						Mage::helper('linksynceparcel')->insertConsignment($order_id,$consignmentNumber,$data,$manifestNumber,$chargeCode,$total_weight,$country);
						Mage::helper('linksynceparcel')->updateArticles($order_id,$consignmentNumber,$consignmentData->articles,$data,$content);
						Mage::helper('linksynceparcel')->insertManifest($manifestNumber);
						
						$labelContent = $consignmentData->lpsLabels->labels->label;
						Mage::helper('linksynceparcel')->generateDocument($consignmentNumber,$labelContent,'label');
					
						$this->_getSession()->addSuccess($this->__('The consignment has been created successfully.'));
					}
					else
					{
						throw new Exception("createConsignment returned empty result");
					}
				}
				catch(Exception $e)
				{
					$error = $this->__('Cannot create consignment, Error: ').$e->getMessage();
					$this->_getSession()->addError($error);
					Mage::log($error, null, 'linksync_eparcel.log', true);
				}
			}
		}
		$this->_redirect("adminhtml/sales_order/view", array('order_id' => $order_id,'active_tab' => 'linksync_eparcel'));
    }
	
	public function saveOrderWeightAction()
    {
		$order_id = $this->getOrder()->getId();
		$address = $this->getOrder()->getShippingAddress();
		$country = $address->getCountry();
		if($country != 'AU') {
			$this->getRequest()->setParam('number_of_articles', 1);
		}
		$number_of_articles = (int)trim($this->getRequest()->getParam('number_of_articles'));
		$data = $this->getRequest()->getParams();
		
		$tempCanConsignments = (int)($number_of_articles/20);
		$canConsignments = $tempCanConsignments;
		$remainArticles = $number_of_articles % 20;
		
		$validate = Mage::helper('linksynceparcel')->validateInternationalConsignment($data, $this->getOrder(), $country);
		if($validate != false) {
			$errors = implode('<br>', $validate);
			$this->_getSession()->addError($errors);
		} else {
			if( $remainArticles > 0)
			{
				$canConsignments++;
			}
			
			for($i=0;$i<$canConsignments;$i++)
			{
				$data['start_index'] = ($i * 20 ) + 1;
				if( ($i+1) <= $tempCanConsignments)
				{
					$data['end_index'] = ($i * 20 ) + 20;
				}
				else
				{
					$data['end_index'] = ($i * 20 ) + $remainArticles;
				}
				
				try
				{
					$articleData = Mage::helper('linksynceparcel')->prepareOrderWeightArticleData($data, $this->getOrder());
					$content = $articleData['content'];
					$chargeCode = $articleData['charge_code'];
					$total_weight = $articleData['total_weight'];
					$consignmentData = Mage::getModel('linksynceparcel/api')->createConsignment($content,0,$chargeCode);

					if($consignmentData)
					{
						$consignmentNumber = $consignmentData->consignmentNumber;
						$manifestNumber = $consignmentData->manifestNumber;
						Mage::helper('linksynceparcel')->insertConsignment($order_id,$consignmentNumber,$data,$manifestNumber,$chargeCode,$total_weight,$country);
						Mage::helper('linksynceparcel')->updateArticles($order_id,$consignmentNumber,$consignmentData->articles,$data,$content);
						Mage::helper('linksynceparcel')->insertManifest($manifestNumber);
				
						$labelContent = $consignmentData->lpsLabels->labels->label;
						Mage::helper('linksynceparcel')->generateDocument($consignmentNumber,$labelContent,'label');
						
						$this->_getSession()->addSuccess($this->__('The consignment has been created successfully.'));
					}
					else
					{
						throw new Exception("createConsignment returned empty result");
					}
				}
				catch(Exception $e)
				{
					$error = $this->__('Cannot create consignment, Error: ').$e->getMessage();
					$this->_getSession()->addError($error);
					Mage::log($error, null, 'linksync_eparcel.log', true);
				}
			}
		}
		$this->_redirect("adminhtml/sales_order/view", array('order_id' => $order_id,'active_tab' => 'linksync_eparcel'));
    }
	
	public function saveDefaultWeightAction()
    {
		$order_id = $this->getOrder()->getId();
		$address = $this->getOrder()->getShippingAddress();
		$country = $address->getCountry();
		if($country != 'AU') {
			$this->getRequest()->setParam('number_of_articles', 1);
		}
		$number_of_articles = (int)trim($this->getRequest()->getParam('number_of_articles'));
		$data = $this->getRequest()->getParams();
		
		$tempCanConsignments = (int)($number_of_articles/20);
		$canConsignments = $tempCanConsignments;
		$remainArticles = $number_of_articles % 20;
		
		$validate = Mage::helper('linksynceparcel')->validateInternationalConsignment($data, $this->getOrder(), $country);
		if($validate != false) {
			$errors = implode('<br>', $validate);
			$this->_getSession()->addError($errors);
		} else {
			if( $remainArticles > 0)
			{
				$canConsignments++;
			}
			
			for($i=0;$i<$canConsignments;$i++)
			{
				$data['start_index'] = ($i * 20 ) + 1;
				if( ($i+1) <= $tempCanConsignments)
				{
					$data['end_index'] = ($i * 20 ) + 20;
				}
				else
				{
					$data['end_index'] = ($i * 20 ) + $remainArticles;
				}
				
				try
				{
					$articleData = Mage::helper('linksynceparcel')->prepareOrderWeightArticleData($data, $this->getOrder());
					$content = $articleData['content'];
					$chargeCode = $articleData['charge_code'];
					$total_weight = $articleData['total_weight'];
					$consignmentData = Mage::getModel('linksynceparcel/api')->createConsignment($content,0,$chargeCode);

					if($consignmentData)
					{
						$consignmentNumber = $consignmentData->consignmentNumber;
						$manifestNumber = $consignmentData->manifestNumber;
						Mage::helper('linksynceparcel')->insertConsignment($order_id,$consignmentNumber,$data,$manifestNumber,$chargeCode,$total_weight,$country);
						Mage::helper('linksynceparcel')->updateArticles($order_id,$consignmentNumber,$consignmentData->articles,$data,$content);
						Mage::helper('linksynceparcel')->insertManifest($manifestNumber);
				
						$labelContent = $consignmentData->lpsLabels->labels->label;
						Mage::helper('linksynceparcel')->generateDocument($consignmentNumber,$labelContent,'label');
					
						$this->_getSession()->addSuccess($this->__('The consignment has been created successfully.'));
					}
					else
					{
						throw new Exception("createConsignment returned empty result");
					}
				}
				catch(Exception $e)
				{
					$error = $this->__('Cannot create consignment, Error: ').$e->getMessage();
					$this->_getSession()->addError($error);
					Mage::log($error, null, 'linksync_eparcel.log', true);
				}
			}
		}
		$this->_redirect("adminhtml/sales_order/view", array('order_id' => $order_id,'active_tab' => 'linksync_eparcel'));
    }

	public function updateConsignmentAction()
    {
		$order_id = $this->getOrder()->getId();
		
		$number_of_articles = (int)trim($this->getRequest()->getParam('number_of_articles'));
		$data = $this->getRequest()->getParams();
		$data['start_index'] = 1;
		$data['end_index'] = $number_of_articles;
				
		try
		{
			$articleData = Mage::helper('linksynceparcel')->prepareArticleData($data, $this->getOrder(),$data['consignment_number']);
			$content = $articleData['content'];
			$chargeCode = $articleData['charge_code'];
			$total_weight = $articleData['total_weight'];
			$consignmentData = Mage::getModel('linksynceparcel/api')->modifyConsignment($content,$data['consignment_number'],$chargeCode);
			if($consignmentData)
			{
				$consignmentNumber = $consignmentData->consignmentNumber;
				$manifestNumber = $consignmentData->manifestNumber;
				Mage::helper('linksynceparcel')->updateConsignment($order_id,$consignmentNumber,$data,$manifestNumber,$chargeCode,$total_weight);
				Mage::helper('linksynceparcel')->updateArticles($order_id,$consignmentNumber,$consignmentData->articles,$data,$content);
				Mage::helper('linksynceparcel')->insertManifest($manifestNumber);
				Mage::helper('linksynceparcel')->removeConsignmentLabels($consignmentNumber);
				
				$labelContent = $consignmentData->lpsLabels->labels->label;
				Mage::helper('linksynceparcel')->generateDocument($consignmentNumber,$labelContent,'label');
				
				$this->_getSession()->addSuccess($this->__('%s: consignment has been updated successfully.',$data['consignment_number']));
				$this->_redirect("adminhtml/sales_order/view", array('order_id' => $order_id,'active_tab' => 'linksync_eparcel'));
			}
		}
		catch(Exception $e)
		{
			$error = $this->__('Cannot update consignment, Error: ').$e->getMessage();
			$this->_getSession()->addError($error);
			Mage::log($error, null, 'linksync_eparcel.log', true);
			$this->_redirect('*/consignment/editConsignment', array('order_id' => $order_id,'consignment_number' => $this->getRequest()->getParam('consignment_number')));
		}
    }
	
	public function updateArticleAction()
    {
		$order_id = $this->getOrder()->getId();
		$data = $this->getRequest()->getParams();
		
		try
		{
			$articleData = Mage::helper('linksynceparcel')->prepareUpdateArticleData($data, $this->getOrder(),$data['consignment_number']);
			$content = $articleData['content'];
			$chargeCode = $articleData['charge_code'];
			$total_weight = $articleData['total_weight'];
			$consignmentData = Mage::getModel('linksynceparcel/api')->modifyConsignment($content,$data['consignment_number'],$chargeCode);
			if($consignmentData)
			{
				$consignmentNumber = $consignmentData->consignmentNumber;
				$manifestNumber = $consignmentData->manifestNumber;
				Mage::helper('linksynceparcel')->updateConsignment($order_id,$consignmentNumber,$data,$manifestNumber,$chargeCode,$total_weight);
				Mage::helper('linksynceparcel')->updateArticles($order_id,$consignmentNumber,$consignmentData->articles,$data,$content);
				Mage::helper('linksynceparcel')->insertManifest($manifestNumber);

				$labelContent = $consignmentData->lpsLabels->labels->label;
				Mage::helper('linksynceparcel')->generateDocument($consignmentNumber,$labelContent,'label');
				
				$this->_getSession()->addSuccess($this->__('The article and consignment has been updated successfully.'));
				$this->_redirect("adminhtml/sales_order/view", array('order_id' => $order_id,'active_tab' => 'linksync_eparcel'));
			}
		}
		catch(Exception $e)
		{
			$error = $this->__('Failed to update article and consignment. ').$e->getMessage();
			$this->_getSession()->addError($error);
			Mage::log($error, null, 'linksync_eparcel.log', true);
            $this->_redirect('*/consignment/editArticle', array('order_id' => $order_id,'consignment_number' => $this->getRequest()->getParam('consignment_number'),'article_number' => $this->getRequest()->getParam('article_number')));
		}
    }
	
	public function newArticleAction()
    {
		$order_id = $this->getOrder()->getId();
		$data = $this->getRequest()->getParams();
	
		try
		{
			$articleData = Mage::helper('linksynceparcel')->prepareAddArticleData($data, $this->getOrder(),$data['consignment_number']);
			$content = $articleData['content'];
			$chargeCode = $articleData['charge_code'];
			$total_weight = $articleData['total_weight'];
			$consignmentData = Mage::getModel('linksynceparcel/api')->modifyConsignment($content,$data['consignment_number'],$chargeCode);
			if($consignmentData)
			{
				$consignmentNumber = $consignmentData->consignmentNumber;
				$manifestNumber = $consignmentData->manifestNumber;
				Mage::helper('linksynceparcel')->updateConsignment($order_id,$consignmentNumber,$data,$manifestNumber,$chargeCode,$total_weight);
				Mage::helper('linksynceparcel')->updateArticles($order_id,$consignmentNumber,$consignmentData->articles,$data,$content);
				Mage::helper('linksynceparcel')->insertManifest($manifestNumber);

				$labelContent = $consignmentData->lpsLabels->labels->label;
				Mage::helper('linksynceparcel')->generateDocument($consignmentNumber,$labelContent,'label');

				$this->_getSession()->addSuccess($this->__('The article has been added successfully.'));
				$this->_redirect("adminhtml/sales_order/view", array('order_id' => $order_id,'active_tab' => 'linksync_eparcel'));
			}
		}
		catch(Exception $e)
		{
			$error = $this->__('Failed to add article: ').$e->getMessage();
			$this->_getSession()->addError($error);
			Mage::log($error, null, 'linksync_eparcel.log', true);
            $this->_redirect('*/consignment/addArticle', array('order_id' => $order_id,'consignment_number' => $this->getRequest()->getParam('consignment_number')));
		}
    }
}
