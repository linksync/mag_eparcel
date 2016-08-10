<?php

class Linksync_Linksynceparcel_Block_Adminhtml_Freeshipping_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

     protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('freeshipping_form', array('legend' => Mage::helper('linksynceparcel')->__('Information')));


        $fieldset->addField('charge_code', 'select', array(
            'label' => Mage::helper('linksynceparcel')->__('Charge Code'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'charge_code',
			'values' => Mage::helper('linksynceparcel')->getChargeCodeOptions(),
        ));
		$fieldset->addField('from_amount', 'text', array(
            'label' => Mage::helper('linksynceparcel')->__('From Cost'),
            'class' => 'required-entry  validate-number',
            'required' => true,
            'name' => 'from_amount'
        ));
		$fieldset->addField('to_amount', 'text', array(
            'label' => Mage::helper('linksynceparcel')->__('To Cost'),
            'class' => 'validate-number',
            'name' => 'to_amount',
			'note' => Mage::helper('linksynceparcel')->__('If this value is zero or empty, then cost range will be assumed as greater than or equal to from cost.'),
        ));
        $fieldset->addField('minimum_amount', 'text', array(
            'label' => Mage::helper('linksynceparcel')->__('Shipping Cost'),
            'class' => 'required-entry  validate-number',
            'required' => true,
            'name' => 'minimum_amount'
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

        if (Mage::getSingleton('adminhtml/session')->getFreeshippingData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getFreeshippingData());
            Mage::getSingleton('adminhtml/session')->setFreeshippingData(null);
        } elseif (Mage::registry('freeshipping_data')) {
            $form->setValues(Mage::registry('freeshipping_data')->getData());
        }
        return parent::_prepareForm();
    }
}