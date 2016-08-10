<?php
class Linksync_Linksynceparcel_Block_Adminhtml_Nonlinksync_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('nonlinksyncGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('linksynceparcel/nonlinksync')->getCollection();
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
		
		$this->addColumn('method', array(
            'header' => Mage::helper('linksynceparcel')->__('Shipping Method'),
            'align' => 'left',
            'width' => '200px',
            'index' => 'method',
			'type' => 'options',
            'options' => Mage::helper('linksynceparcel')->getNonlinksyncShippingTypes(),
        ));
		
		$this->addColumn('charge_code', array(
            'header' => Mage::helper('linksynceparcel')->__('Charge Code'),
            'align' => 'left',
            'width' => '200px',
            'index' => 'charge_code',
			'type' => 'options',
            'options' => Mage::helper('linksynceparcel')->getChargeCodeValues(true),
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
        $this->getMassactionBlock()->setFormFieldName('nonlinksync');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'=> Mage::helper('linksynceparcel')->__('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => Mage::helper('linksynceparcel')->__('Are you sure?')
        ));

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

}