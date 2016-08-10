<?php

class Linksync_Linksynceparcel_Block_Adminhtml_Nonlinksync extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_nonlinksync';
        $this->_blockGroup = 'linksynceparcel';
        $this->_headerText = Mage::helper('linksynceparcel')->__('Assign Shipping Types');
        $this->_addButtonLabel = Mage::helper('linksynceparcel')->__('Add New');
        parent::__construct();
    }

}
