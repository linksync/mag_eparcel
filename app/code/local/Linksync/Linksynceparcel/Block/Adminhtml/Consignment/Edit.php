<?php
class Linksync_Linksynceparcel_Block_Adminhtml_Consignment_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'order_id';
        $this->_controller = 'consignment';
        $this->_mode = 'create';

        parent::__construct();
    }

    public function getOrder()
    {
		$order_id = $this->getRequest()->getParam('order_id');
        return Mage::getModel('sales/order')->load($order_id);
    }

    public function getHeaderText()
    {
        $header = Mage::helper('sales')->__('Edit Article #%s for Consignment #%s', $this->getRequest()->getParam('article_number'), $this->getRequest()->getParam('consignment_number'));
        return $header;
    }

    public function getBackUrl()
    {
        return $this->getUrl('adminhtml/sales_order/view', array('order_id'=>$this->getOrder()->getId(),'active_tab' => 'linksync_eparcel'));
    }
	
	public function getSaveUrl()
    {
        $url = $this->getUrl('linksynceparcel/consignment/updateArticle/', array('order_id' => $this->getOrder()->getId(), 'consignment_number' => $this->getRequest()->getParam('consignment_number'), 'article_number' => $this->getRequest()->getParam('article_number')));
		return $url;
    }
	
	public function getConsignment()
	{
		return Mage::helper('linksynceparcel')->getConsignment($this->getRequest()->getParam('order_id'),$this->getRequest()->getParam('consignment_number'));
	}
	
	public function getArticle()
	{
		return Mage::helper('linksynceparcel')->getArticle($this->getRequest()->getParam('order_id'),$this->getRequest()->getParam('consignment_number'),$this->getRequest()->getParam('article_number'));
	}
}
