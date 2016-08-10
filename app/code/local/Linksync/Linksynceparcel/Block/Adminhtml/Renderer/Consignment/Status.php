<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Consignment_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
	{
		$status =  $row->getData($this->getColumn()->getIndex());
		
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');
	    $table = $resource->getTableName('sales_order_status');

		$query = "select label from {$table} where status = '{$status}'";
		return $readConnection->fetchOne($query);
	}
}
