<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Consignment_Service extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$value =  $row->getData('order_consignment');
		$values = explode('_',$value);
		$orderId = $values[0];
		$order = Mage::getModel('sales/order')->load($orderId);
		$incrementId = $order->getIncrementId();
		$address = $order->getShippingAddress();
		$country = $address->getCountry();
		$chargeCode = $row->getData('general_linksynceparcel_shipping_chargecode');
		if(!$chargeCode) {
			$chargeCode = Mage::helper('linksynceparcel')->getChargeCode($order);
		}
		$chargeCodes = Mage::helper('linksynceparcel')->getChargeCodes();
		$chargeCodeData = $chargeCodes[$chargeCode];
		$method =  $row->getData('shipping_description');
		$shipping_method = Mage::helper('linksynceparcel')->getNonlinksyncShippingTypeChargecode($method);
		if($country != "AU" && !$shipping_method) {
			$color = '4487f5';
			$service_string = 'Int.';
		} else {
			switch($chargeCodeData['serviceType']) {
				case 'express':
					$color = 'f66a1e';
					break;
				case 'standard':
					$color = 'ffa10c';
					break;
				case 'international':
					$color = '4487f5';
					break;
			}
			
			$service_string = ucfirst($chargeCodeData['service']);
		}
		
		$html = '<p id="increment_'. $value .'" data-incrementid="'. $incrementId .'" style="background-color: #'. $color .';color: #fff;text-align: center;border-radius: 10px;font-weight: 600;">'. $service_string .'</p>';
		return $html;
	}
}
