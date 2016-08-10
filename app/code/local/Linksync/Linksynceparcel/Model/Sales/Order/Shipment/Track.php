<?php
class Linksync_Linksynceparcel_Model_Sales_Order_Shipment_Track extends Mage_Sales_Model_Order_Shipment_Track
{
    /**
     * Retrieve detail for shipment track
     *
     * @return string
     */
    public function getNumberDetail()
    {
        $carrierInstance = false;Mage::getSingleton('shipping/config')->getCarrierInstance($this->getCarrierCode());
        if (!$carrierInstance) {
            $custom = array();
            $custom['title'] = $this->getTitle();
			$custom['number'] = $this->getTrackNumber();
			
			if($this->getCarrierCode() == 'linksynceparcel')
			{
            	$custom['number'] = '<a href="http://auspost.com.au/track/track.html?id='.$custom['number'].'" target="_blank">'.$custom['number'].'</a>';
			}
            return $custom;
        } else {
            $carrierInstance->setStore($this->getStore());
        }

        if (!$trackingInfo = $carrierInstance->getTrackingInfo($this->getNumber())) {
            return Mage::helper('sales')->__('No detail for number "%s"', $this->getNumber());
        }

        return $trackingInfo;
    }
}