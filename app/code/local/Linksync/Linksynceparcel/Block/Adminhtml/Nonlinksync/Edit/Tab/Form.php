<?php

class Linksync_Linksynceparcel_Block_Adminhtml_Nonlinksync_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

     protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('nonlinksync_form', array('legend' => Mage::helper('linksynceparcel')->__('Information')));

		$fieldset->addField('method', 'select', array(
            'label' => Mage::helper('linksynceparcel')->__('Method'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'method',
			'values' => Mage::helper('linksynceparcel')->getNonlinksyncShippingTypeOptions(),
			'note' => 'NOTE - when you create a new shipping type for your site IT WILL NOT BE AVAILABLE HERE UNTIL THERE ARE ORDERS USING THE NEW SHIPPING METHOD. We suggestion you create a test order using the new shipping method and then return to this screen to assign the Charge Code to it.'
        ));
		
        $fieldset->addField('charge_code', 'select', array(
            'label' => Mage::helper('linksynceparcel')->__('Charge Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'charge_code',
			'values' => Mage::helper('linksynceparcel')->getChargeCodeOptions(true),
        ));

        if (Mage::getSingleton('adminhtml/session')->getNonlinksyncData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getNonlinksyncData());
            Mage::getSingleton('adminhtml/session')->setNonlinksyncData(null);
        } elseif (Mage::registry('nonlinksync_data')) {
            $form->setValues(Mage::registry('nonlinksync_data')->getData());
        }
        return parent::_prepareForm();
    }
}