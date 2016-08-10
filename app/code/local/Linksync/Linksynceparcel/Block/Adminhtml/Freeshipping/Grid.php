<?php
class Linksync_Linksynceparcel_Block_Adminhtml_Freeshipping_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('freeshippingGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('linksynceparcel/freeshipping')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('id', array(
            'header' => Mage::helper('linksynceparcel')->__('ID'),
            'align' => 'right',
            'width' => '10px',
            'index' => 'id',
        ));
		
		$this->addColumn('charge_code', array(
            'header' => Mage::helper('linksynceparcel')->__('Charge Code'),
            'align' => 'left',
            'width' => '200px',
            'index' => 'charge_code',
        ));
		
		$this->addColumn('from_amount', array(
            'header' => Mage::helper('linksynceparcel')->__('Cost Range'),
            'align' => 'left',
            'width' => '200px',
            'index' => 'from_amount',
			'renderer' => 'Linksync_Linksynceparcel_Block_Adminhtml_Renderer_Freeshipping_Range',
			'filter' => false,
			'sortable' => false
        ));
		
		$this->addColumn('minimum_amount', array(
            'header' => Mage::helper('linksynceparcel')->__('Shipping Cost'),
            'align' => 'left',
            'width' => '200px',
            'index' => 'minimum_amount',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('linksynceparcel')->__('Status'),
            'align' => 'center',
            'width' => '30px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                1 => 'Enabled',
                2 => 'Disabled',
            ),
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('linksynceparcel')->__('Action'),
            'width' => '30px',
			'align' => 'center',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('linksynceparcel')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));
        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
	
	protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('freeshipping');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('linksynceparcel')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('linksynceparcel')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('catalog/product_status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('linksynceparcel')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('catalog')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

}