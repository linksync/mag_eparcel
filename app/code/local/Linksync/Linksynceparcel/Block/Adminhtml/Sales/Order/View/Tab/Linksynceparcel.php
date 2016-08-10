<?php
class Linksync_Linksynceparcel_Block_Adminhtml_Sales_Order_View_Tab_Linksynceparcel
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _construct()
    {
        parent::_construct();
		$use_order_total_weight = (int)Mage::getStoreConfig('carriers/linksynceparcel/use_order_total_weight');
		$use_article_dimensions = (int)Mage::getStoreConfig('carriers/linksynceparcel/use_article_dimensions');
		if($use_order_total_weight == 1 && $use_article_dimensions != 1)
		{
			$this->setTemplate('linksynceparcel/consignment/order_weight_view.phtml');
		}
		elseif($use_order_total_weight != 1 && $use_article_dimensions != 1)
		{
			$this->setTemplate('linksynceparcel/consignment/default_weight_view.phtml');
		}
		elseif($use_order_total_weight == 1 && $use_article_dimensions == 1)
		{
			$this->setTemplate('linksynceparcel/consignment/order_weight_articles_view.phtml');
		}
		else
		{
        	$this->setTemplate('linksynceparcel/consignment/view.phtml');
		}
    }

    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    public function getTabLabel()
    {
        return Mage::helper('sales')->__('linksync eParcel Shipments');
    }

    public function getTabTitle()
    {
        return Mage::helper('sales')->__('linksync eParcel Shipments');
    }

    public function canShowTab()
    {
		$shipAddress = $this->getOrder()->getShippingAddress();
		$country = $shipAddress->getCountry();
		$method = $this->getOrder()->getShippingDescription();
		$shipping_method = Mage::helper('linksynceparcel')->getNonlinksyncShippingTypeChargecode($method);
        if (!$shipping_method) {
			return false;
		}
        if ($this->getOrder()->getIsVirtual()) {
            return false;
        }
        return true;
    }

    public function isHidden()
    {
		$active = (int)Mage::getStoreConfig('carriers/linksynceparcel/active');
		if($active == 1)
		{
			if($this->getOrder()->getState() != 'canceled')
			{
				$code = Mage::helper('linksynceparcel')->getOrderCarrier($this->getOrder()->getId());
				if($code == 'linksynceparcel')
					return false;
			}
		}
		return true;
    }
	
	public function getConsignmentCreateUrl()
	{
		$url = $this->getUrl('linksynceparcel/consignment/create/');
		$url .= 'order_id/'.$this->getOrder()->getId();
		return $url;
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
	
	public function getConsignmentLabelCreateUrl($consignmentNumber)
	{
		$url = $this->getUrl('linksynceparcel/consignment/labelCreate/', array('order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber));
		return $url;
	}
	
	public function getConsignmentReturnLabelCreateUrl($consignmentNumber)
	{
		$url = $this->getUrl('linksynceparcel/consignment/returnLabelCreate/', array('order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber));
		return $url;
	}
	
	public function getConsignmentDeleteUrl($consignmentNumber)
	{
		$url = $this->getUrl('linksynceparcel/consignment/deleteConsignment/', array('order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber));
		return $url;
	}
	
	public function getConsignmentEditUrl($consignmentNumber)
	{
		$url = $this->getUrl('linksynceparcel/consignment/editConsignment/', array('order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber));
		return $url;
	}
	
	
	public function getArticleDeleteUrl($consignmentNumber, $articleNumber)
	{
		$url = $this->getUrl('linksynceparcel/consignment/deleteArticle/', array('order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber, 'article_number' => $articleNumber));
		return $url;
	}
		
	public function getArticleEditUrl($consignmentNumber, $articleNumber)
	{
		$url = $this->getUrl('linksynceparcel/consignment/editArticle/', array('order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber, 'article_number' => $articleNumber));
		return $url;
	}
	
	public function getArticleAddUrl($consignmentNumber)
	{
		$url = $this->getUrl('linksynceparcel/consignment/addArticle/', array('order_id' => $this->getOrder()->getId(), 'consignment_number' => $consignmentNumber));
		return $url;
	}
	
	public function isReturnLabelFileExists($consignmentNumber)
	{
		$filename = $consignmentNumber.'.pdf';
		$filepath = Mage::getBaseDir().DS.'media'.DS.'linksync'.DS.'label'.DS.'returnlabels'.DS.$filename;
		return file_exists($filepath);
	}
}
