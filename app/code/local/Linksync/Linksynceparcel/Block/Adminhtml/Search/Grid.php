<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Search_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('searchGrid');
        $this->setDefaultSort('consignment_number');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
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
        $collection = Mage::getModel('linksynceparcel/consignment')->getCollection();
		$collection
			->getSelect()
			->join(array('order' => Mage::getSingleton('core/resource')->getTableName('sales_flat_order')),
                    'main_table.order_id = order.entity_id')
			->join(array('order_address' => Mage::getSingleton('core/resource')->getTableName('sales_flat_order_address')),
                    'order.shipping_address_id = order_address.entity_id')
			->joinLeft(array('manifest' => Mage::getSingleton('core/resource')->getTableName('linksync_linksynceparcel_manifest')),
                    'main_table.manifest_number = manifest.manifest_number', array('despatch_date'))
			->joinLeft(array('article' => Mage::getSingleton('core/resource')->getTableName('linksync_linksynceparcel_article')),
                    'main_table.consignment_number = article.consignment_number', array('number_of_articles' => 'COUNT(article_number)'))
			->columns(new Zend_Db_Expr("CONCAT(`order_address`.`firstname`, ' ',`order_address`.`lastname`) AS fullname"))
			->columns('main_table.weight as total_consignment_weight')
			->group('main_table.consignment_number');
		/*echo $collection->getSelect()->__toString();
		exit;*/
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
 
    protected function _prepareColumns()
    {
		$this->addColumn('increment_id', array(
            'header' => Mage::helper('linksynceparcel')->__('Order No.'),
            'align' => 'left',
            'index' => 'increment_id',
            'sortable' => true,
			'renderer' => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Search_Number'
        ));
		
		 $this->addColumn('customer_name', array(
            'header' => Mage::helper('linksynceparcel')->__('Customer Name'),
            'align' => 'left',
            'index' => 'fullname',
            'sortable' => true,
			'filter_condition_callback' => array($this, 'customerNameCondition')
        ));
		 
		$this->addColumn('state', array(
            'header' => Mage::helper('linksynceparcel')->__('State'),
            'align' => 'center',
            'index' => 'region',
            'sortable' => true
        ));
		
		$this->addColumn('postcode', array(
            'header' => Mage::helper('linksynceparcel')->__('Postcode'),
            'align' => 'center',
            'index' => 'postcode',
            'sortable' => true
        ));
		
		$this->addColumn('country', array(
            'header' => Mage::helper('linksynceparcel')->__('Country'),
            'align' => 'center',
            'index' => 'country_id',
            'sortable' => true
        ));
		
		$this->addColumn('total_consignment_weight', array(
          'header'    => Mage::helper('linksynceparcel')->__('Weight'),
          'align'     =>'center',
          'index'     => 'total_consignment_weight',
		  'filter' => false,
          'sortable' => true
        ));
		
		$this->addColumn('consignment_number', array(
          'header'    => Mage::helper('linksynceparcel')->__('Consignment Number'),
          'align'     =>'center',
          'index'     => 'consignment_number',
		  'filter_index'     => 'main_table.consignment_number',
		  'sortable' => true,
		  'renderer' => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Search_Number'
      ));
		
		$this->addColumn('add_date', array(
		  'header'    => Mage::helper('linksynceparcel')->__('Created Date'),
		  'align'     =>'center',
		  'type'	  => 'datetime',
		  'index'     => 'add_date',
		  'sortable' => true,
		  'renderer' => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Search_Date'
		));
		
        $this->addColumn('despatch_date', array(
		  'header'    => Mage::helper('linksynceparcel')->__('Despatch Date'),
		  'align'     =>'center',
		  'type'	  => 'datetime',
		  'index'     => 'despatch_date',
		  'sortable' => true,
		  'renderer' => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Search_Date'
		));
	
      $this->addColumn('number_of_articles', array(
          'header'    => Mage::helper('linksynceparcel')->__('No. of Articles'),
          'align'     =>'center',
          'index'     => 'number_of_articles',
		  'sortable' => true,
		  'filter_condition_callback' => array($this, 'totalArticlesCondition')
      ));
	  
	  $this->addColumn('label', array(
          'header'    => Mage::helper('linksynceparcel')->__('Label'),
          'align'     =>'center',
          'index'     => 'label',
		  'sortable'  => false,
		  'filter'	  => false,
		  'renderer'  => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Search_Labelprint'
      ));
	  
	  $this->addColumn('customdocs', array(
          'header'    => Mage::helper('linksynceparcel')->__('Documents'),
          'align'     =>'center',
          'index'     => 'customdocs',
		  'sortable'  => false,
		  'filter'	  => false,
		  'renderer'  => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Search_Customdocsprint'
      ));
	  
	  $this->addColumn('track_url', array(
          'header'    => Mage::helper('linksynceparcel')->__('Track'),
          'align'     =>'center',
          'index'     => 'consignment_number',
		  'sortable' => false,
		  'filter' => false,
		  'renderer' => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Search_Track'
      ));

        return parent::_prepareColumns();
    }
	
	protected function customerNameCondition($collection, $column) 
	{
 		if (!$value = $column->getFilter()->getValue()) {
			return;
		}
	 
	 	$consignmentNumbers = array();
		foreach(Mage::getModel('linksynceparcel/consignment')->getCollection() as $key => $item) 
		{
			$consignment_number = $item->getConsignmentNumber();
			$orderId =  $item->getOrderId();
			$address = Mage::helper('linksynceparcel')->getShippingAdress($orderId);
			$firstname =  $address['firstname'];
			$lastname =  $address['lastname'];
			$fullname = $firstname.' '.$lastname;
			
			if (preg_match('/'.$value.'/i',$fullname))
				$consignmentNumbers[] = $consignment_number;
		}
		
		if (count($consignmentNumbers) == 0) 
 			$this->getCollection()->addFieldToFilter('main_table.consignment_number', array('nin' => $consignmentNumbers));
		else
		{
			$this->getCollection()->addFieldToFilter('main_table.consignment_number', array('in' => $consignmentNumbers));
		}
	}
	
	protected function totalArticlesCondition($collection, $column) 
	{
 		if (!$value = $column->getFilter()->getValue())
			return;
	 
		if (!is_numeric($value)) 
 			return;

		$consignmentNumbers = array();
		foreach(Mage::getModel('linksynceparcel/consignment')->getCollection() as $key => $item) 
		{
			$consignment_number = $item->getConsignmentNumber();
			$orderId =  $item->getOrderId();
			$articles = Mage::helper('linksynceparcel')->getArticles($orderId, $consignment_number);
			$totalArticles = count($articles);
			
			if ($totalArticles == $value) 
				$consignmentNumbers[] = $consignment_number;
		}
		
		if (count($consignmentNumbers) == 0) 
 			$this->getCollection()->addFieldToFilter('main_table.consignment_number', array('nin' => $consignmentNumbers));
		else
		{
			$this->getCollection()->addFieldToFilter('main_table.consignment_number', array('in' => $consignmentNumbers));
		}
	}
}
