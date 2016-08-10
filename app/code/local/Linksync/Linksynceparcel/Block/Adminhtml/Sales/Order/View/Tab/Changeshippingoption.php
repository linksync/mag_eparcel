<?php

class Linksync_Linksynceparcel_Block_Adminhtml_Sales_Order_View_Tab_Changeshippingoption
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('linksynceparcel/changeshippingoption.phtml');
    }

    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    public function getTabLabel()
    {
        return Mage::helper('sales')->__('Change Shipping Option');
    }

    public function getTabTitle()
    {
        return Mage::helper('sales')->__('Change Shipping Option');
    }

    public function canShowTab()
    {
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
			if(Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/change_shipping_method') == 1)
			{
				if($this->getOrder()->getState() != 'canceled' && $this->getOrder()->getState() != 'complete')
				{
					return false;
				}
			}
		}
		return true;
    }
}
