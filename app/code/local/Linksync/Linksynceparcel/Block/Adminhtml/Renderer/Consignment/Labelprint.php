<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Consignment_Labelprint extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$value =  $row->getData('is_label_created');
		if($value == 1)
		{
			$label =  $row->getData('label');
			$labelLink = Mage::helper('linksynceparcel')->getConsignmentLabelUrl();
			$html = '<a href="'.$labelLink.$label.'?'.time().'" target="_blank" >Print</a>';
		}
		else
		{
			$html ='&nbsp;';
		}
		return $html;
	}
}
