<?php

class SM_Reviews_Block_Adminhtml_Reviews_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('reviewGrid');
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('review_id');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection() {
        $collection = Mage::getModel("smreviews/reviews")->getCollection();
         if ($vendor = Mage::helper('smvendors')->getVendorLogin()) {
             $collection->addFilter('vendor_id', $vendor->getId());
             $collection->addFilter('status', 1);
         }
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('review_id', array(
            'header' => Mage::helper('adminhtml')->__('Review #'),
            'index' => 'review_id',
            'align' => 'right',
            'width' => '50px'
        ));
        $this->addColumn('created_at', array(
            'header' => Mage::helper('adminhtml')->__('Created On'),
            'index' => 'created_at',
        	'type' => 'datetime',
            'width' => '150px'
        ));
        $this->addColumn('order_id', array(
            'header' => Mage::helper('adminhtml')->__('Order #'),
            'index' => 'order_id',
            'width' => '150px'
        ));
        $this->addColumn('customer_name', array(
            'header' => Mage::helper('adminhtml')->__('Customer Name'),
            'index' => 'customer_name',
            'width' => '150px'
        ));        
        $this->addColumn('customer_email', array(
            'header' => Mage::helper('adminhtml')->__('Customer Email'),
            'index' => 'customer_email',
            'width' => '150px'
        ));        
        if (!Mage::helper('smvendors')->getVendorLogin()) {
            $this->addColumn('vendor_id', array(
                'header' => Mage::helper('adminhtml')->__('Vendor Name'),
                'index' => 'vendor_id',
                'renderer' => 'smreviews/adminhtml_widget_grid_column_renderer_vendor',
                'width' => '150px'
            ));
        }
        $this->addColumn('rating', array(
            'header' => Mage::helper('adminhtml')->__('Rating'),
            'index' => 'rating',
            'align' => 'left',
            'renderer' => 'smreviews/adminhtml_widget_grid_column_renderer_rating',
            'width' => '100px'
        ));
        $this->addColumn('comment', array(
            'header' => Mage::helper('adminhtml')->__('Comment'),
            'index' => 'comment'
        ));
//        if (!Mage::helper('smvendors')->getVendorLogin()) {
            $this->addColumn('status', array(
                'header' => Mage::helper('adminhtml')->__('Status'),
                'index' => 'status',
                'align' => 'center',
                'type' => 'options',
                'options' => Mage::getSingleton('smreviews/reviews_status')->getOptionArray(),
                'width' => '80px'
            ));
//        }
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction() {
        if (!Mage::helper('smvendors')->getVendorLogin()) {
            $this->setMassactionIdField('review_id');
            $this->getMassactionBlock()->setFormFieldName('review');

            $this->getMassactionBlock()->addItem('delete', array(
                'label' => Mage::helper('adminhtml')->__('Delete'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => Mage::helper('adminhtml')->__('Are you sure?')
            ));

            $this->getMassactionBlock()->addItem('status', array(
                'label' => Mage::helper('adminhtml')->__('Change status'),
                'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
                'additional' => array(
                    'visibility' => array(
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => Mage::helper('adminhtml')->__('Status'),
                        'values' => Mage::getSingleton('smreviews/reviews_status')->getOptionArray()
                    )
                )
            ));
        }

        return $this;
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function getRowUrl($row) {
    	return $this->getUrl('*/*/edit', array('id' => $row->getReviewId()));
    }

}