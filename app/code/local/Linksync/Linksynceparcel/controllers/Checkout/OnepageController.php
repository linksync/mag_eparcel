<?php
include_once("Mage/Checkout/controllers/OnepageController.php");
class Linksync_Linksynceparcel_Checkout_OnepageController extends Mage_Checkout_OnepageController
{
	public function saveShippingMethodAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping_method', '');
			
			$code = 'nomethod';
			if(!empty($data))
			{
				$methods = Mage::helper('linksynceparcel')->collectShippingData($data);
				if(is_array($methods) && isset($methods[0]))
				{
					$code = $methods[0];
				}
			}
			
			if($code == 'linksynceparcel' && !Mage::getStoreConfig('carriers/linksynceparcel/signature_required'))
			{
				$authority_to_leave	= $this->getRequest()->getPost('authority_to_leave', '');
				$special_instructions	= $this->getRequest()->getPost('special_instructions', '');
				if(!$authority_to_leave)
				{
					 $result['error'] = 'Please select authority to leave option';
				}
				else if(!$special_instructions)
				{
					 $result['error'] = 'Please enter instructions';
				}
				else if(strlen($special_instructions) > 128)
				{
					 $result['error'] = 'Special instructions: maximum allowed 128 characters';
				}
			}
			
			if (!$result) {
				$result = $this->getOnepage()->saveShippingMethod($data);
				// $result will contain error data if shipping method is empty
				if (!$result) {
					
					Mage::getSingleton('checkout/session')->setLinksyncLinksynceparcelAuthorityToLeaveSession($authority_to_leave);
					Mage::getSingleton('checkout/session')->setLinksyncLinksynceparcelSpecialInstructionsSession(base64_encode($special_instructions));
					
					Mage::dispatchEvent(
						'checkout_controller_onepage_save_shipping_method',
						 array(
							  'request' => $this->getRequest(),
							  'quote'   => $this->getOnepage()->getQuote()));
					$this->getOnepage()->getQuote()->collectTotals();
					$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
					
	
					$result['goto_section'] = 'payment';
					$result['update_section'] = array(
						'name' => 'payment-method',
						'html' => $this->_getPaymentMethodsHtml()
					);
				}
			}
			else
			{
				$result['message'] = $result['error'];
				$result['error'] = true;
			}
            $this->getOnepage()->getQuote()->collectTotals()->save();
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
}
?>
