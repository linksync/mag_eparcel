<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Freeshipping_Range extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$from =  $row->getData($this->getColumn()->getIndex());
		$to =  $row->getData('to_amount');
		$html = '';
		if(!empty($from) && $from > 0)
		{
			if(!empty($to) && $to > 0)
			{
				$html = $from .' - '.$to;
			}
			else
			{
				$html =  '>= '. $from;
			}
		}
		return $html;
	}
}
