<?php
class Linksync_Linksynceparcel_Block_Backend_Config_Requirement extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setTemplate('linksynceparcel/requirement.phtml');
        return $this->toHtml();
    }
}
