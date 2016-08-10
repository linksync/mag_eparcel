<?php
class Linksync_Linksynceparcel_Block_Adminhtml_Search extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_search';
        $this->_blockGroup = 'linksynceparcel';
        $this->_headerText = Mage::helper('linksynceparcel')->__('Consignment Search Results');
		
        parent::__construct();
		$this->removeButton('add');
    }
}
