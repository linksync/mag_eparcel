<?php
class Linksync_Linksynceparcel_Model_Carrier_Linksynceparcel extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface {

    protected $_code = 'linksynceparcel';
	
	protected $_default_condition_name = 'package_weight';
	
	protected $_conditionNames = array();
	
	public function __construct()
	{
		parent::__construct();
		foreach ($this->getCode('condition_name') as $k=>$v) {
			$this->_conditionNames[] = $k;
		}
	}

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) 
		{
			return false;
		}

		if (!$request->getConditionName()) 
		{
			$request->setConditionName($this->getConfigData('condition_name') ? $this->getConfigData('condition_name') : $this->_default_condition_name);
		}
		
		/*if(!$request->getDestPostcode())
		{
			$error = Mage::getModel("shipping/rate_result_error");
			$error->setCarrier('linksynceparcel');
			$error->setCarrierTitle($this->getConfigData('title'));
			$error->setErrorMessage('Please enter delivery address postal code to view available shipping methods');
			return $error;
		}
*/
		$result = Mage::getModel('shipping/rate_result');
			
		$rates = $this->getRate($request);

        if(is_array($rates))
        {
            foreach ($rates as $rate)
            {
               if (!empty($rate) && $rate['price'] >= 0) 
			   {
               		$method = Mage::getModel('shipping/rate_result_method');

                    $method->setCarrier('linksynceparcel');
                    $method->setCarrierTitle($this->getConfigData('title'));

                    $method->setMethod($this->getChargeCode($rate));
                   
                    $method->setMethodChargeCode($rate['charge_code']);
					 
                    $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);

					$freeshippingResult = Mage::helper('linksynceparcel')->getFreeshipping($rate['charge_code'],$request->getBaseSubtotalInclTax());
					if ($freeshippingResult)
					{
						if($freeshippingResult['to_amount'] > 0 )
						{
							if($request->getBaseSubtotalInclTax() <= $freeshippingResult['to_amount'])
							{
								$freeshippingForMinimumAmount = $freeshippingResult['minimum_amount'];
							}
						}
						else
						{
							$freeshippingForMinimumAmount = $freeshippingResult['minimum_amount'];
						}
						
						if($freeshippingForMinimumAmount == 0)
						{
							$shippingPrice = 0;
							$method->setMethodTitle(Mage::helper('shipping')->__('Free Shipping'));
						}
						else
						{
							$shippingPrice = $freeshippingForMinimumAmount;
							$method->setMethodTitle($rate['delivery_type']);
						}
					} 
					else 
					{
						 $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
						 $method->setMethodTitle($rate['delivery_type']);
					}

                    $method->setPrice($shippingPrice);
                    $method->setCost($rate['cost']);
                    $method->setDeliveryType($rate['delivery_type']);

                    $result->append($method);
                }
            }
        }
        else
        {
            if (!empty($rates) && $rates['price'] >= 0) {
                $method = Mage::getModel('shipping/rate_result_method');

                $method->setCarrier('linksynceparcel');
                $method->setCarrierTitle($this->getConfigData('title'));

                $method->setMethod('bestway');
                $method->setMethodTitle($this->getConfigData('name'));

                $method->setMethodChargeCode($rates['charge_code']);
                
                $shippingPrice = $this->getFinalPriceWithHandlingFee($rates['price']);
				
				$freeshippingForMinimumAmount = Mage::helper('linksynceparcel')->getFreeshipping($rate['charge_code']);
				if ($freeshippingForMinimumAmount && ($request->getBaseSubtotalInclTax() >= $freeshippingForMinimumAmount))
				{
					$shippingPrice = 0;
					$method->setMethodTitle(Mage::helper('shipping')->__('Free Shipping'));
				} else {
					 $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
					 $method->setMethodTitle($rate['delivery_type']);
				}

                $method->setPrice($shippingPrice);
                $method->setCost($rates['cost']);
                $method->setDeliveryType($rates['delivery_type']);

                $result->append($method);
            }
        }

		return $result;
    }
	
	public function getRate(Mage_Shipping_Model_Rate_Request $request)
	{
		return Mage::getResourceModel('linksynceparcel/carrier_linksynceparcel')->getRate($request);
	}
	
	protected function getChargeCode($rate)
    {
	    return $rate['charge_code'];
    }
	
	public function getCode($type, $code='')
	{
		$codes = array(

		    'condition_name'=>array(
		        'package_weight' => Mage::helper('shipping')->__('Weight vs. Destination'),
		        'package_value'  => Mage::helper('shipping')->__('Price vs. Destination'),
		        'package_qty'    => Mage::helper('shipping')->__('# of Items vs. Destination'),
		    ),

		    'condition_name_short'=>array(
		        'package_weight' => Mage::helper('shipping')->__('Weight (and above)'),
		        'package_value'  => Mage::helper('shipping')->__('Order Subtotal (and above)'),
		        'package_qty'    => Mage::helper('shipping')->__('# of Items (and above)'),
		    ),

		);

		if (!isset($codes[$type])) {
		    throw Mage::exception('Mage_Shipping', Mage::helper('shipping')->__('Invalid Table Rate code type: %s', $type));
		}

		if (''===$code) {
		    return $codes[$type];
		}

		if (!isset($codes[$type][$code])) {
		    throw Mage::exception('Mage_Shipping', Mage::helper('shipping')->__('Invalid Table Rate code for type %s: %s', $type, $code));
		}

		return $codes[$type][$code];
	}

    public function getAllowedMethods()
    {
        return array('linksynceparcel' => $this->getConfigData('name'));
    }
	
	public function getTrackingInfo($number)
	{
		$custom = array();
		$custom['title'] = $this->getConfigData('title');
		$custom['number'] = '<a href="http://auspost.com.au/track/track.html?id='.$number.'" target="_blank">'.$number.'</a>';
		return $custom;
	}
}