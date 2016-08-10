<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Consignment_Nextmanifest extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$valid =  $row->getData('is_next_manifest');
		if($valid)
		{
			$imgLink = $this->getSkinUrl("linksynceparcel/images/icon-enabled.png");
			$html = '<img src="'.$imgLink.'" />';
		}
		else
		{
			$html = '&nbsp;';
		}
		
		return $html;
	}
}
