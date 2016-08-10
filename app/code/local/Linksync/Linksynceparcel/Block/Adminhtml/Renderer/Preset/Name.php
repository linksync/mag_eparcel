<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Preset_Name extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$name =  $row->getData($this->getColumn()->getIndex());
		$weight =  $row->getData('weight');
		$height =  $row->getData('height');
		$width =  $row->getData('width');
		$length =  $row->getData('length');
		$html = "$name ({$weight}kg - {$height}x{$width}x{$length})";
		return $html;
	}
}
