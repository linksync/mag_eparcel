<?php

class Linksync_Linksynceparcel_Block_Adminhtml_Freeshipping_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('freeshipping_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('linksynceparcel')->__('Rule'));
    }

    protected function _beforeToHtml() {
        $this->addTab('freeshipping_section', array(
            'label' => Mage::helper('linksynceparcel')->__('Information'),
            'title' => Mage::helper('linksynceparcel')->__('Information'),
            'content' => $this->getLayout()->createBlock('linksynceparcel/adminhtml_freeshipping_edit_tab_form')->toHtml(),
        ));
        return parent::_beforeToHtml();
    }

}