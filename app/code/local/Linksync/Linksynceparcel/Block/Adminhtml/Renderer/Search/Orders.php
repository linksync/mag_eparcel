<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Search_Orders extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$value =  $row->getData($this->getColumn()->getIndex());
				
		$order = Mage::getModel('sales/order')->load($value);
		$incrementId = $order->getIncrementId();
		$orderLink = $this->getUrl('adminhtml/sales_order/view', array('order_id'=>$value));
		if($i==0)
		{
			$html .= '<a href="'.$orderLink.'">'.$incrementId.'</a>';
		}
		else
		{
			$html .= ', <a href="'.$orderLink.'">'.$incrementId.'</a>';
		}
		
		return $html;
	}
}
