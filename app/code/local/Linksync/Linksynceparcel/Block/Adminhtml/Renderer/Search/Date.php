<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Search_Date extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$date =  $row->getData($this->getColumn()->getIndex());
		if($date)
		{
			$dateTimestamp = Mage::getModel('core/date')->timestamp(strtotime($date));
			return date('m/d/Y H:i:s', $dateTimestamp);
		}
	}
}
