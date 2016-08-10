<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Search_Number extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$value =  $row->getData($this->getColumn()->getIndex());
		$orderId =  $row->getData('order_id');
		$orderLink = $this->getUrl('adminhtml/sales_order/view', array('order_id'=>$orderId,'active_tab' => 'linksync_eparcel'));
		$html = '<a href="'.$orderLink.'" >'.$value.'</a>';
		return $html;
	}
}
