<?php
class Linksync_Linksynceparcel_Block_Adminhtml_System_Config_Sendlog extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setTemplate('linksynceparcel/sendlog.phtml');
        return $this->toHtml();
    }
}
