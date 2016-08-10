<?php
class Linksync_Linksynceparcel_ChangeoptionController extends Mage_Adminhtml_Controller_Sales_Shipment
{
	public function shippingAction()
    {
		try
		{
			$order_id = $this->getRequest()->getParam('order_id');
			if($order_id > 0)
			{
				$order = Mage::getModel("sales/order")->load($order_id);
				$linksynceparcel_shipping_option = trim($this->getRequest()->getParam('linksynceparcel_shipping_option'));
				$linksynceparcel_shipping_option = base64_decode($linksynceparcel_shipping_option);
				$options = explode('###',$linksynceparcel_shipping_option);
				$order->setShippingMethod(trim($options[0]));
				$order->setShippingDescription(trim($options[1]));
				$order->save();
				$this->_getSession()->addSuccess($this->__('Shipping option has been changed successfully.'));
			}
			else
			{
				$this->_getSession()->addError($this->__('Order not loaded'));
			}
			$this->_redirect("adminhtml/sales_order/view", array('order_id' => $order_id));
		}
		catch(Exception $e)
		{
			$this->_getSession()->addError($this->__('Shipping option failed to changed, Error:').$e->getMessage());
            $this->_redirect("adminhtml/sales_order/view", array('order_id' => $order_id));
		}
    }
}
