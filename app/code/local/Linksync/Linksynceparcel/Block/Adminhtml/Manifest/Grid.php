<?php
 
class Linksync_Linksynceparcel_Block_Adminhtml_Manifest_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('manifestGrid');
        $this->setDefaultSort('manifest_number');
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
		if(Mage::helper('linksynceparcel')->getStoreConfig('carriers/linksynceparcel/manifest_sync') == 1)
		{
			Mage::helper('linksynceparcel')->getManifestNumber();
		}
        $collection = Mage::getModel('linksynceparcel/manifest')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
 
    protected function _prepareColumns()
    {
		 $this->addColumn('manifest_number', array(
            'header' => Mage::helper('linksynceparcel')->__('Manifest Number'),
            'align' => 'center',
            'index' => 'manifest_number',
			'sortable' => true,
			'renderer' => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Manifest_Number'
        ));
		
        $this->addColumn('despatch_date', array(
		  'header'    => Mage::helper('linksynceparcel')->__('Despatch Date'),
		  'align'     =>'center',
		  'type'	  => 'datetime',
		  'index'     => 'despatch_date',
		  'sortable' => true,
		  'renderer' => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Manifest_Date'
		));
	
       $this->addColumn('number_of_consignments', array(
          'header'    => Mage::helper('linksynceparcel')->__('No. of Consignments'),
          'align'     =>'center',
          'index'     => 'number_of_consignments',
		  'sortable' => true,
		  'filter' => false,
		  'renderer' => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Manifest_Totalconsignments'
      ));
     
      $this->addColumn('number_of_articles', array(
          'header'    => Mage::helper('linksynceparcel')->__('No. of Articles'),
          'align'     =>'center',
          'index'     => 'number_of_articles',
		  'sortable' => true,
		  'filter' => false,
		  'renderer' => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Manifest_Totalarticles'
      ));
	  
	  $this->addColumn('label', array(
          'header'    => Mage::helper('linksynceparcel')->__('Print'),
          'align'     =>'center',
          'index'     => 'label',
		  'sortable'  => false,
		  'filter'	  => false,
		  'renderer'  => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Manifest_Labelprint'
      ));

        return parent::_prepareColumns();
    }
	
	protected function _prepareMassaction()
    {
        $this->setMassactionIdField('manifest_id');
        $this->getMassactionBlock()->setFormFieldName('manifest_id');
		$this->getMassactionBlock()->setUseSelectAll(false); 
		
		$this->getMassactionBlock()->addItem('generatelabels', array(
             'label'=> Mage::helper('linksynceparcel')->__('Generate Manifest Summary'),
             'url'  => $this->getUrl('*/*/massGenerateLabels')
        ));

        return $this;
    }
}
