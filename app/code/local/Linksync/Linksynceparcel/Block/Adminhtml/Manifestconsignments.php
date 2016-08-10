<?php
class Linksync_Linksynceparcel_Block_Adminhtml_Manifestconsignments extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_manifestconsignments';
        $this->_blockGroup = 'linksynceparcel';
		
		$manifest_number = $this->getRequest()->getParam('manifest');
		$text = Mage::helper('linksynceparcel')->__('Manifest Consignments - %s', $manifest_number);
		
		$manifest = Mage::helper('linksynceparcel')->getManifest($manifest_number);
		
		if($manifest && $manifest['despatch_date'] != '')
		{
			$dateTimestamp = Mage::getModel('core/date')->timestamp(strtotime($manifest['despatch_date']));
			$text .= ', despatched at '.date('m/d/Y H:i:s',$dateTimestamp);
		}
		
        $this->_headerText = $text;
		
        parent::__construct();
		$this->removeButton('add');
    }
}
