<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Consignment_Number extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$value =  $row->getData('consignment_number');
		if(!$value)
		{
			$order =  $row->getData('order_consignment');
			$values = explode('_',$order);
			$orderId = $values[0];
			$value = Mage::helper('linksynceparcel')->__('Create Consignment');
		}
		else
		{
			$orderId =  $row->getData('order_id');
		}
		$orderLink = $this->getUrl('adminhtml/sales_order/view', array('order_id'=>$orderId,'active_tab' => 'linksync_eparcel'));
		$html = '<a href="'.$orderLink.'">'.$value.'</a>';
		return $html;
	}
}
