<?php

class SM_Dropship_Block_Adminhtml_Shipping_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('shippingRateGrid');
		$this->setUseAjax(true);
        $this->setDefaultSort('rate_id');
        $this->setSaveParametersInSession(true);
    }
    
	protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('smdropship/shipping_multiflatrate_collection');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
		
	   $this->addColumn('rate_id', array(
            'header'    => Mage::helper('smdropship')->__('ID'),
            'width'     => '50px',
            'index'     => 'rate_id',
            'type'  => 'number',
        ));
        $this->addColumn('title', array(
                'header'    => Mage::helper('smdropship')->__('Title'),
                'width'     => '100px',
                'index'     => 'title'
        ));
        
		$this->addColumn('price', array(
            'header' => Mage::helper('smdropship')->__('Shipping Fee'),
            'align' => 'center',
            'index' => 'price',
            'sortable' => false,
            'width' => '150px',
        ));

        $this->addColumn('active', array(
            'header'    =>  Mage::helper('smdropship')->__('Status'),
            'width'     =>  '100',
            'index'     =>  'active',
            'type'      =>  'options',
            'options'   =>  array(
				1 => Mage::helper('catalog')->__('Enabled'),
                0 => Mage::helper('catalog')->__('Disabled'),
			),
        ));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('customer')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('customer')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('customer')->__('Excel XML'));
        return $this;
    }
	
	
	public function getGridUrl()
    {
        return $this->getUrl('*/*/gridVendor', array('_current'=> true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id'=>$row->getId()));
    }
}