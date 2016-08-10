<?php

class Linksync_Linksynceparcel_Adminhtml_SearchController extends Mage_Adminhtml_Controller_Action 
{
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('linksynceparcel/search');
	}
	
    public function indexAction() 
	{
        $this->loadLayout();
        $this->_setActiveMenu('linksync/linksynceparcel/search');
		$this->getLayout()->getBlock('head')->setTitle($this->__('eParcel Search'));
        $this->renderLayout();
    }
}