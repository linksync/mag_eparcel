<?php
class Linksync_Linksynceparcel_Block_Adminhtml_Consignment extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_consignment';
        $this->_blockGroup = 'linksynceparcel';
        $this->_headerText = Mage::helper('linksynceparcel')->__('Consignments');
		
		$data = array(
		   'label' => 'Despatch',
		   'onclick' => "setLocationConfirmDialogNew('".$this->getUrl('linksynceparcel/adminhtml_consignment/despatch')."')"
        );
		
		if(!Mage::helper('linksynceparcel')->isCurrentMainfestHasConsignmentsForDespatch())
		{
			$data['disabled'] = 'disabled';
		}
		
		$display = false;
		$active = (int)Mage::getStoreConfig('carriers/linksynceparcel/active');
		if($active == 1) { $display = true; }
		$isDisplaylps = Mage::helper('linksynceparcel')->isDisplayConsignmentViewTableLps();
		if($display) { $display = $isDisplaylps; }
		$isDisplayShip = Mage::helper('linksynceparcel')->isDisplayConsignmentViewTableShip();
		if($display) { $display = $isDisplayShip; }
		
		if($display)
		{
			Mage_Adminhtml_Block_Widget_Container::addButton('despatch', $data, 0, 200,  'header', 'header');
		}
		
        parent::__construct();
		$this->removeButton('add');
    }
}
