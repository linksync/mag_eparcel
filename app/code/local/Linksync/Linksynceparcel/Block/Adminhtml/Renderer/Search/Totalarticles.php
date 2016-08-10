<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Search_Totalarticles extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$consignment_number =  $row->getData($this->getColumn()->getIndex());
		$orderId =  $row->getData('order_id');
		$articles = Mage::helper('linksynceparcel')->getArticles($orderId, $consignment_number);
		return count($articles);
	}
}
