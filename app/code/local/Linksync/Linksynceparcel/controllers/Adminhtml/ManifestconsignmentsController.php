<?php

class Linksync_Linksynceparcel_Adminhtml_ManifestconsignmentsController extends Mage_Adminhtml_Controller_Action 
{
	protected function _isAllowed()
	{
		return true;
	}
	
    public function indexAction() 
	{
        $this->loadLayout();
        $this->_setActiveMenu('linksync/linksynceparcel/manifest');
        $this->renderLayout();
		
    }
}