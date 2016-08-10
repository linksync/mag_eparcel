<?php
class Linksync_Linksynceparcel_Block_Adminhtml_Freeshipping_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'linksynceparcel';
        $this->_controller = 'adminhtml_freeshipping';

        $this->_updateButton('save', 'label', Mage::helper('linksynceparcel')->__('Save Rule'));
        $this->_updateButton('delete', 'label', Mage::helper('linksynceparcel')->__('Delete Rule'));

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
                ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('freeshipping_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'freeshipping_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'freeshipping_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText() {
        if (Mage::registry('freeshipping_data') && Mage::registry('freeshipping_data')->getId()) {
            return Mage::helper('linksynceparcel')->__("Edit Rule", $this->htmlEscape(Mage::registry('freeshipping_data')->getTitle()));
        } else {
            return Mage::helper('linksynceparcel')->__('Add Rule');
        }
    }

}