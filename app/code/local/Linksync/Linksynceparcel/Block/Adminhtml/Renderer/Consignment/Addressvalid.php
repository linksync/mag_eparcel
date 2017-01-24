<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Consignment_Addressvalid extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$value =  $row->getData('order_consignment');
		$values = explode('_',$value);
		$orderId = $values[0];
		$order = Mage::getModel('sales/order')->load($orderId);
		$address = $order->getShippingAddress();
		$country = $address->getCountry();
		if($country == 'AU') {
			$isValid = Mage::helper('linksynceparcel')->isOrderAddressValid($orderId);
			$valid =  $row->getData($this->getColumn()->getIndex());
			if($valid)
			{
				$imgLink = $this->getSkinUrl("linksynceparcel/images/icon-enabled.png");
			}
			elseif($isValid == 1) 
			{
				$imgLink = $this->getSkinUrl("linksynceparcel/images/icon-enabled.png");
			}
			else
			{
				$imgLink = $this->getSkinUrl("linksynceparcel/images/cancel_icon.gif");
			}
		} else {
			$imgLink = $this->getSkinUrl("linksynceparcel/images/icon-hold.png");
		}
		$html = '<img src="'.$imgLink.'" />';
		return $html;
	}
}
