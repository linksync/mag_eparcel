<?php

class Linksync_Linksynceparcel_Block_Adminhtml_Freeshipping extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_freeshipping';
        $this->_blockGroup = 'linksynceparcel';
        $this->_headerText = Mage::helper('linksynceparcel')->__('Manage Freeshipping Rule');
        $this->_addButtonLabel = Mage::helper('linksynceparcel')->__('Add New Rule');
        parent::__construct();
    }

}
