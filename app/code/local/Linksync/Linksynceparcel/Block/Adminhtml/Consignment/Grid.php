<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Consignment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('order_consignment');
        $this->setDefaultSort('increment_id');
        $this->setDefaultDir('DESC');
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
		$status_condition = 'main_table.status = "processing" OR main_table.status = "pending" OR';
		$display_choosen_status = (int)Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/display_choosen_status');
		if($display_choosen_status == 1)
		{
			$chosen_statuses = Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/chosen_statuses');
			if(!empty($chosen_statuses))
			{
				$chosen_statuses = explode(',',$chosen_statuses);
				if(count($chosen_statuses) > 0)
				{
					$status_condition = '';
					foreach($chosen_statuses as $chosen_status)
					{
						if(!empty($chosen_status))
							$status_condition .= 'main_table.status="'.$chosen_status.'" OR ';
					}
				}
			}
		}
		
		$where = ' (main_table.shipping_method like "%linksynceparcel%" OR 
						   		(
								case 
								when (select count(*) from '.Mage::getSingleton('core/resource')->getTableName('linksync_linksynceparcel_nonlinksync').' where method = main_table.shipping_description and  charge_code != "none") > 0 
								then 1 
								else 0
								end
								) > 0
						) AND ('.$status_condition.'
							(case when (select count(*) from '.Mage::getSingleton('core/resource')->getTableName('linksync_linksynceparcel_consignment').' where order_id = main_table.entity_id) > 0 then (select count(*) from '.Mage::getSingleton('core/resource')->getTableName('linksync_linksynceparcel_consignment').' where order_id = main_table.entity_id and despatched = 0) else 0 end) > 0
					)';
		if(Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/apply_to_all') && Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/default_chargecode') != '')
		{
			$ids = Mage::helper('linksynceparcel')->isAppltytoallOptionIsON();
			
			if($ids) {
				$im_ids = implode(',', $ids);
			} else {
				$im_ids = 0;
			}
			$where = "(main_table.entity_id IN (". $im_ids ."))";
		}
		
		$collection = Mage::getModel('linksynceparcel/consignmentui')->getCollection();
		$collection
			->getSelect()
			->joinLeft(array('order_address' => Mage::getSingleton('core/resource')->getTableName('sales_flat_order_address')),
					'main_table.shipping_address_id = order_address.entity_id')
			->reset(Zend_Db_Select::COLUMNS)
			->columns(new Zend_Db_Expr("CONCAT(`order_address`.`firstname`, ' ',`order_address`.`lastname`) AS fullname"))
			->columns('CONCAT(main_table.entity_id, "_",IFNULL(c.consignment_number,0)) as order_consignment')
			->columns('main_table.customer_firstname')
			->columns('main_table.customer_lastname')
			->columns('main_table.is_address_valid')
			->columns('main_table.increment_id')
			->columns('main_table.shipping_method')
			->columns('main_table.status')
			->columns('main_table.shipping_description')
			->columns('c.general_linksynceparcel_shipping_chargecode as general_linksynceparcel_shipping_chargecode')
			->columns("(case when c.consignment_number != '' then ( select count(*) from ".Mage::getSingleton('core/resource')->getTableName('linksync_linksynceparcel_article')." where consignment_number = c.consignment_number) else null end) as number_of_articles")
			->columns('is_address_valid as is_not_open')
			->joinLeft(array('c' => Mage::getSingleton('core/resource')->getTableName('linksync_linksynceparcel_consignment')),
					'main_table.entity_id = c.order_id and c.despatched=0')
			->where($where)
			;
		
		// echo $collection->getSelect()->__toString();
		// exit;
		$this->setCollection($collection);
		return parent::_prepareCollection();
    }
 
    protected function _prepareColumns()
    {
		$this->addColumn('service', array(
            'header' => Mage::helper('linksynceparcel')->__('Service'),
            'align' => 'center',
            'index' => 'c.general_linksynceparcel_shipping_chargecode',
			'type' => 'options',
			'options' => array('standard' => "Std.", 'express' => "Exp.", 'international' => "Int."),
			'renderer' => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Consignment_Service',
            'sortable' => false,
			'filter_condition_callback' => array($this, 'serviceFilter')
        ));
		
		$this->addColumn('increment_id', array(
            'header' => Mage::helper('linksynceparcel')->__('Order'),
            'align' => 'center',
            'index' => 'increment_id',
            'sortable' => true,
			'renderer' => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Consignment_Ordernumber'
        ));
		
        $this->addColumn('customer_name', array(
          'header'    => Mage::helper('linksynceparcel')->__('Ship to'),
          'align'     =>'left',
		  'width'	=> '150px',
          'index'     => 'fullname',
		  'filter' => false,
          'sortable' => true
        ));
		
		$display_order_status = (int)Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/display_order_status');
		if($display_order_status == 1)
		{
			$this->addColumn('status', array(
			  'header'    => Mage::helper('linksynceparcel')->__('Status'),
			  'align'     =>'center',
			  'index'     => 'status',
			  'filter' => false,
			  'sortable' => false,
			  'renderer' => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Consignment_Status'
			));
		}
		
		$this->addColumn('weight', array(
          'header'    => Mage::helper('linksynceparcel')->__('Weight'),
          'align'     =>'center',
          'index'     => 'weight',
		  'filter' => false,
          'sortable' => true
        ));
	
        $this->addColumn('is_address_valid', array(
          'header'    => Mage::helper('linksynceparcel')->__('Address Valid'),
          'align'     =>'center',
          'index'     => 'is_address_valid',
          'type' => 'options',
            'options' => array(
                1 => 'Yes',
                0 => 'No',
            ),
			'sortable' => false,
			'renderer' => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Consignment_Addressvalid'
        ));
     
      $this->addColumn('consignment_number', array(
          'header'    => Mage::helper('linksynceparcel')->__('Consignment Number'),
          'align'     =>'center',
          'index'     => 'c.consignment_number',
		  'sortable' => true,
		  'renderer' => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Consignment_Number'
      ));
	  
	  $this->addColumn('shipping_method', array(
          'header'    => Mage::helper('linksynceparcel')->__('Delivery Type'),
          'align'     =>'left',
          'index'     => 'shipping_method',
		  'type' => 'options',
          'options' => Mage::helper('linksynceparcel')->getDeliveryTypeOptions2(),
		  'renderer' => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Consignment_Shippingmethod',
		  'sortable' => true,
		  'filter_condition_callback' => array($this, 'shippingMethodCondition')
      ));
	  
	  $this->addColumn('is_label_printed', array(
          'header'    => Mage::helper('linksynceparcel')->__('Labels Printed?'),
          'align'     =>'center',
          'index'     => 'c.is_label_printed',
		  'type' => 'options',
          'options' => array(
                1 => 'Yes',
                0 => 'No',
          ),
		  'sortable' => false,
		  'renderer' => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Consignment_Labelprinted'
        ));
	 
	   $this->addColumn('is_next_manifest', array(
          'header'    => Mage::helper('linksynceparcel')->__('Next Manifest?'),
          'align'     =>'center',
          'index'     => 'c.is_next_manifest',
		  'type' => 'options',
          'options' => array(
                1 => 'Yes',
                0 => 'No',
          ),
		  'sortable' => false,
		  'renderer' => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Consignment_Nextmanifest'
      ));
	   
	  $this->addColumn('number_of_articles', array(
          'header'    => Mage::helper('linksynceparcel')->__('No. of Articles'),
          'align'     =>'center',
          'index'     => 'number_of_articles',
		  'sortable' => true,
		  'filter' => false,
      ));

		 $this->addColumn('date_add', array(
		  'header'    => Mage::helper('linksynceparcel')->__('Date Created'),
		  'align'     =>'center',
		  'index'     => 'add_date',
		  'sortable' => true,
		  'filter' => false,
		  'renderer' => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Consignment_Date'
		));
         
        return parent::_prepareColumns();
    }
	
	protected function serviceFilter($collection, $column)
	{
		if (!$value = $column->getFilter()->getValue())
			return;
		
		$ids = Mage::helper('linksynceparcel')->isInternationalServiceFilter($value);
		$implodes = 0;
		if(!empty($ids)) {
			$implodes = implode(',', $ids);
		}
		
		$this->getCollection()->getSelect()->where( "(main_table.entity_id IN (". $implodes ."))" );
		// echo $collection->getSelect()->__toString();
		// exit;
	}
	
	protected function shippingMethodCondition($collection, $column) 
	{
 		if (!$value = $column->getFilter()->getValue())
			return;
		
		$this->getCollection()->getSelect()->where( "(main_table.shipping_method like '%_$value' or c.general_linksynceparcel_shipping_chargecode = '$value')" );
	}
	
	protected function _prepareMassaction()
    {
        $this->setMassactionIdField('order_consignment');
        $this->getMassactionBlock()->setFormFieldName('order_consignment');
		$this->getMassactionBlock()->setUseSelectAll(false); 

		$active = (int)Mage::getStoreConfig('carriers/linksynceparcel/active');
		if($active == 1)
		{
			$presetsArray = array();
			$use_order_total_weight = (int)Mage::getStoreConfig('carriers/linksynceparcel/use_order_total_weight');
			$use_article_dimensions = (int)Mage::getStoreConfig('carriers/linksynceparcel/use_article_dimensions');
			if($use_order_total_weight == 1)
			{
				$presetsArray['order_weight'] = 'Use Order Weight';
			}
			
			if($use_article_dimensions == 1)
			{
				$presets = Mage::getModel('linksynceparcel/preset')
						->getCollection()
						->addFilter('status', array('eq' => 1));
				if($presets->count() > 0)
				{
					foreach($presets as $preset)
					{
						$presetsArray[$preset->getName().'<=>'.$preset->getWeight().'<=>'.$preset->getHeight().'<=>'.$preset->getWidth().'<=>'.$preset->getLength()] = $preset->getName(). ' ('.$preset->getWeight().'kg - '.$preset->getHeight().'x'.$preset->getWidth().'x'.$preset->getLength().')';
					}
				}
			}
			
			if(count($presetsArray) > 0)
			{
				$statuses = array( 0 => 'No', 1 => 'Yes');
				$classificationoptions = array( 991 => 'Other', 32 => 'Commercial', 31 => 'Gift', 91 => 'Document' );
				
				$countryCollection = Mage::getModel('directory/country')->getCollection();
				$countries = array();
				foreach($countryCollection as $country) {
					$countries[$country->getCountryId()] = $country->getName();
				}
				
				$this->getMassactionBlock()->addItem('create', array(
					 'label'=> Mage::helper('linksynceparcel')->__('Create Consignment'),
					 'url'  => $this->getUrl('*/*/massCreateConsignment'),
					 'additional' => array(
							'presets' => array(
								 'name' => 'articles_type',
								 'type' => 'select',
								 'class' => 'required-entry articles_type-ui',
								 'label' => Mage::helper('catalog')->__('Article Presets'),
								 'values' => $presetsArray
							 ),
					 )
				));
			}
			
			$this->getMassactionBlock()->addItem('generatelabels', array(
				 'label'=> Mage::helper('linksynceparcel')->__('Generate Labels'),
				 'url'  => $this->getUrl('*/*/massGenerateLabels')
			));
			
			$this->getMassactionBlock()->addItem('unassign', array(
				 'label'=> Mage::helper('linksynceparcel')->__('Remove from Manifest'),
				 'url'  => $this->getUrl('*/*/massUnassignConsignment'),
				 'confirm' => Mage::helper('linksynceparcel')->__('Are you sure?')
			));
			
			$this->getMassactionBlock()->addItem('assign', array(
				 'label'=> Mage::helper('linksynceparcel')->__('Add to Manifest'),
				 'url'  => $this->getUrl('*/*/massAssignConsignment')
			));
			
			$this->getMassactionBlock()->addItem('delete', array(
				 'label'=> Mage::helper('linksynceparcel')->__('Delete Consignment'),
				 'url'  => $this->getUrl('*/*/massDeleteConsignment'),
				 'confirm' => Mage::helper('linksynceparcel')->__('Are you sure?')
			));
			
			if(trim(Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/mark_despatch')) == 1)
			{
				$this->getMassactionBlock()->addItem('markdespatched', array(
					 'label'=> Mage::helper('linksynceparcel')->__('Mark as Despatched'),
					 'url'  => $this->getUrl('*/*/massMarkDespatched'),
					 'confirm' => Mage::helper('linksynceparcel')->__('Are you sure?')
				));
			}
		}
        return $this;
    }
}
