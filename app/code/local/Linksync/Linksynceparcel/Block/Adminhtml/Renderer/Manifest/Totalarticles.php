<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Manifest_Totalarticles extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$value =  $row->getData($this->getColumn()->getIndex());
		/*$despatch_date =  $row->getData('despatch_date');
		if(!$despatch_date)
		{
			$value = (int)Mage::getSingleton('core/session')->getNumberOfArticles();
		}*/
		return $value;
	}
}
