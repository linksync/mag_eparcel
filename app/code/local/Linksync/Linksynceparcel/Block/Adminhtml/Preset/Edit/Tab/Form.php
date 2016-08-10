<?php

class Linksync_Linksynceparcel_Block_Adminhtml_Preset_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

     protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('preset_form', array('legend' => Mage::helper('linksynceparcel')->__('Information')));


        $fieldset->addField('name', 'text', array(
            'label' => Mage::helper('linksynceparcel')->__('Preset Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'name',
        ));
		$fieldset->addField('weight', 'text', array(
            'label' => Mage::helper('linksynceparcel')->__('Weight (Kgs)'),
            'class' => 'required-entry  validate-number',
            'required' => true,
            'name' => 'weight'
        ));
		$fieldset->addField('height', 'text', array(
            'label' => Mage::helper('linksynceparcel')->__('Height (cm)'),
            'class' => 'required-entry  validate-number',
            'required' => true,
            'name' => 'height'
        ));
        $fieldset->addField('width', 'text', array(
            'label' => Mage::helper('linksynceparcel')->__('Width (cm)'),
            'class' => 'required-entry  validate-number',
            'required' => true,
            'name' => 'width'
        ));
		$fieldset->addField('length', 'text', array(
            'label' => Mage::helper('linksynceparcel')->__('Length (cm)'),
            'class' => 'required-entry  validate-number',
            'required' => true,
            'name' => 'length'
        ));

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('linksynceparcel')->__('Status'),
            'name' => 'status',
            'values' => array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('linksynceparcel')->__('Enabled'),
                ),
                array(
                    'value' => 2,
                    'label' => Mage::helper('linksynceparcel')->__('Disabled'),
                ),
            ),
        ));

        if (Mage::getSingleton('adminhtml/session')->getPresetData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getPresetData());
            Mage::getSingleton('adminhtml/session')->setPresetData(null);
        } elseif (Mage::registry('preset_data')) {
            $form->setValues(Mage::registry('preset_data')->getData());
        }
        return parent::_prepareForm();
    }
}