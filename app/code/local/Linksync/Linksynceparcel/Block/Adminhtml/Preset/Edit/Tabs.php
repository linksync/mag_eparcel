<?php

class Linksync_Linksynceparcel_Block_Adminhtml_Preset_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('preset_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('linksynceparcel')->__('Rule'));
    }

    protected function _beforeToHtml() {
        $this->addTab('preset_section', array(
            'label' => Mage::helper('linksynceparcel')->__('Information'),
            'title' => Mage::helper('linksynceparcel')->__('Information'),
            'content' => $this->getLayout()->createBlock('linksynceparcel/adminhtml_preset_edit_tab_form')->toHtml(),
        ));
        return parent::_beforeToHtml();
    }

}