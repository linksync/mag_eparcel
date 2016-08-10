<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Manifest_Number extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$value =  $row->getData('manifest_number');
		if($value)
		{
			$link = $this->getUrl('linksynceparcel/adminhtml_manifestconsignments/index/', array('manifest'=>$value));
			$html = '<a href="'.$link.'">'.$value.'</a>';
		}
		else
		{
			$html = '';
		}
		
		return $html;
	}
}
