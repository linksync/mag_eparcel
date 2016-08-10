<?php
class Linksync_Linksynceparcel_Model_Consignment extends Mage_Core_Model_Abstract
{
	public function _construct()
    {
        parent::_construct();
        $this->_init('linksynceparcel/consignment');
    }
	
   	public function getTotalItems($order)
	{
		if($order)
		{
			$total = 0;
			$orderedItems = $order->getAllItems();
			foreach($orderedItems as $item)
			{
				$total++;
			}
			return $total;
		}
		return 0;
	}
	
	public function getTotalQty($order)
	{
		if($order)
		{
			$total = 0;
			$orderedItems = $order->getAllItems();
			foreach($orderedItems as $item)
			{
				$total += $item->getQtyOrdered();
			}
			return $total;
		}
		return 0;
	}
}
