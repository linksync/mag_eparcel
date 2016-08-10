<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Search_Track extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$value =  $row->getData($this->getColumn()->getIndex());
		$html = '<a href="http://auspost.com.au/track/track.html?id='.$value.'" target="_blank" >Click</a>';
		return $html;
	}
}
