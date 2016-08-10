<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Search_Labelprint extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$value =  $row->getData($this->getColumn()->getIndex());
		if($value)
		{
			$labelLink = Mage::helper('linksynceparcel')->getConsignmentLabelUrl();
			$html = '<a class="print_label" lang="'.$row->getData('consignment_number').'" href="'.$labelLink.$value.'?'.time().'" target="_blank" >View</a>';
		}
		else
		{
			$html ='&nbsp;';
		}
		return $html;
	}
}
