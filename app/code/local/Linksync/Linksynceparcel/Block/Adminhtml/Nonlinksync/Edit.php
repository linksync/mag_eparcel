<?php
class Linksync_Linksynceparcel_Block_Adminhtml_Nonlinksync_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'linksynceparcel';
        $this->_controller = 'adminhtml_nonlinksync';

        $this->_updateButton('save', 'label', Mage::helper('linksynceparcel')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('linksynceparcel')->__('Delete'));

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
                ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('nonlinksync_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'nonlinksync_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'nonlinksync_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText() {
        if (Mage::registry('nonlinksync_data') && Mage::registry('nonlinksync_data')->getId()) {
            return Mage::helper('linksynceparcel')->__("Edit", $this->htmlEscape(Mage::registry('nonlinksync_data')->getTitle()));
        } else {
            return Mage::helper('linksynceparcel')->__('Add');
        }
    }

}