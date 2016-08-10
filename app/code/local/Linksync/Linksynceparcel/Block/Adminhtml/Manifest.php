<?php
class Linksync_Linksynceparcel_Block_Adminhtml_Manifest extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_manifest';
        $this->_blockGroup = 'linksynceparcel';
        $this->_headerText = Mage::helper('linksynceparcel')->__('Manifests');
		
        parent::__construct();
		$this->removeButton('add');
    }
}
