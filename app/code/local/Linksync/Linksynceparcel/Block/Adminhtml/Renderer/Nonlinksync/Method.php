<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Nonlinksync_Method extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$method = $row->getData('method');
		$m = stripslashes($method);
		return $m;
	}
}
