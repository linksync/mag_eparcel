<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Manifestconsignments_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('manifestconsignmentsGrid');
        $this->setDefaultSort('consignment_number');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
		$this->setFilterVisibility(false);
    }
    
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function  getSearchButtonHtml()
    {
        return parent::getSearchButtonHtml();
    }
 
    protected function _prepareCollection()
    {
		$manifest_number = $this->getRequest()->getParam('manifest');
        $collection = Mage::getModel('linksynceparcel/consignment')->getCollection();
		$collection->addFieldToFilter('manifest_number', array('eq' => $manifest_number ));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
 
    protected function _prepareColumns()
    {
		$this->addColumn('order_id', array(
            'header' => Mage::helper('linksynceparcel')->__('Order Number'),
            'align' => 'left',
            'index' => 'order_id',
            'sortable' => false,
		 	'filter' => false,
			'renderer' => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Search_Orders'
        ));
				
		 $this->addColumn('consignment_number', array(
            'header' => Mage::helper('linksynceparcel')->__('Consignment Number'),
            'align' => 'center',
            'index' => 'consignment_number',
            'sortable' => false
        ));
  
		$this->addColumn('number_of_articles', array(
		  'header'    => Mage::helper('linksynceparcel')->__('No. of Articles'),
		  'align'     =>'center',
		  'index'     => 'consignment_number',
		  'sortable' => false,
		  'renderer' => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Search_Totalarticles'
		));
		
		$this->addColumn('label', array(
		  'header'    => Mage::helper('linksynceparcel')->__('Label'),
		  'align'     =>'center',
		  'index'     => 'label',
		  'sortable'  => false,
		  'filter'	  => false,
		   'renderer'  => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Search_Labelprint'
		));

        return parent::_prepareColumns();
    }
}
