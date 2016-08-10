<?php
class Linksync_Linksynceparcel_Block_Adminhtml_Consignment_Create extends Mage_Adminhtml_Block_Widget_Form_Container
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
        $header = Mage::helper('sales')->__('Create Consignment for Order #%s', $this->getOrder()->getRealOrderId());
        return $header;
    }

    public function getBackUrl()
    {
        return $this->getUrl('adminhtml/sales_order/view', array('order_id'=>$this->getOrder()->getId(),'active_tab' => 'linksync_eparcel'));
    }
	
	public function isFormDisplay()
	{
		if(isset($_REQUEST['number_of_articles']))
		{
			$number_of_articles = trim($this->getRequest()->getParam('number_of_articles'));
			if(empty($number_of_articles))
			{
				;
			}
			else if(!is_numeric($number_of_articles))
			{
				;
			}
			else if($number_of_articles < 1)
			{
				;
			}
			else
			{
				return true;
			}
		}
		return false;
	}
	
	public function getTotalItems()
	{
		$order = $this->getOrder();
		return Mage::getModel('linksynceparcel/consignment')->getTotalItems($order);
	}
	
	public function getNumberOfArticles()
	{
		return trim($this->getRequest()->getParam('number_of_articles'));
	}
}
