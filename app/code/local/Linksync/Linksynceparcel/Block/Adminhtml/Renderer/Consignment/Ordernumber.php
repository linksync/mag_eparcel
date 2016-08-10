<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Consignment_Ordernumber extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$value =  $row->getData('order_consignment');
		$values = explode('_',$value);
		$orderId = $values[0];
		$order = Mage::getModel('sales/order')->load($orderId);
		$incrementId = $order->getIncrementId();
		$orderLink = $this->getUrl('adminhtml/sales_order/view', array('order_id'=>$orderId));
		$html = '<a href="'.$orderLink.'">'.$incrementId.'</a>';
		return $html;
	}
}
