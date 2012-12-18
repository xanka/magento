<?php

class SM_Vendors_Block_Adminhtml_Banner_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    public function __construct() {
        parent::__construct();
        $this->setId('bannerGrid');
        $this->setDefaultSort('creation_time');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel('smvendors/banner')->getCollection();
		if($vendor = Mage::helper('smvendors')->getVendorLogin()){
			$collection->addVendorToFilter($vendor->getId());
		}
		else{
			$collection->addVendorToSelect();
		}
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('banner_id', array(
                'header'    => Mage::helper('smvendors')->__('ID'),
                'align'     =>'right',
                'width'     => '50px',
                'index'     => 'id',
        ));
		
		$this->addColumn('image', array(
            'header' => Mage::helper('smvendors')->__('Banner'),
            'align' => 'center',
            'index' => 'image',
            'renderer' => 'smvendors/adminhtml_widget_grid_column_renderer_image',
            'escape' => true,
            'sortable' => false,
            'width' => '150px',
        ));

        $this->addColumn('title', array(
                'header'    => Mage::helper('smvendors')->__('Title'),
                'align'     =>'left',
                'index'     => 'title',
        ));
		
		if(!($vendor = Mage::helper('smvendors')->getVendorLogin())){
			$this->addColumn('vendor_name', array(
					'header'    => Mage::helper('smvendors')->__('Vendor'),
					'align'     =>'left',
					'index'     => 'vendor_name',
			));
		}

        $this->addColumn('is_active', array(
                'header'    => Mage::helper('smvendors')->__('Status'),
                'align'     => 'left',
                'width'     => '80px',
                'index'     => 'active',
                'type'      => 'options',
                'options'   => array(
                        1 => Mage::helper('smvendors')->__('Enabled'),
                        0 => Mage::helper('smvendors')->__('Disabled')
                ),
        ));

        $this->addColumn('action',
                array(
                'header'    =>  Mage::helper('smvendors')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                        array(
                                'caption'   => Mage::helper('smvendors')->__('Edit'),
                                'url'       => array('base'=> '*/*/edit'),
                                'field'     => 'id'
                        ),
                        array(
                                'caption'   => Mage::helper('smvendors')->__('Delete'),
                                'url'       => array('base'=> '*/*/delete'),
                                'field'     => 'id'
                        )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}