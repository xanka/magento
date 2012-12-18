<?php
class SM_Vendors_Block_Adminhtml_Sales_Shipment_Grid extends Mage_Adminhtml_Block_Sales_Shipment_Grid
{
    /**
     * Prepare and set collection of grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        if($vendor = Mage::helper('smvendors')->getVendorLogin()) {
            $grandParent = get_parent_class(get_parent_class($this));
            $collection = Mage::getResourceModel($this->_getCollectionClass());
            
            $collection->join(
                    array('smo' => 'smvendors/order'),
                    "main_table.order_id = smo.order_id
                    AND smo.vendor_id = {$vendor->getId()}
                    AND main_table.vendor_id = {$vendor->getId()}",
                    array('vendor_increment_id' => 'smo.increment_id')
            );
                        
            $this->setCollection($collection);
            call_user_func(array($grandParent, '_prepareCollection'));
        } else {
            return parent::_prepareCollection();
        }
    }

    /**
     * Prepare and add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        if($vendor = Mage::helper('smvendors')->getVendorLogin()) {
            $this->addColumn('increment_id', array(
                'header'    => Mage::helper('sales')->__('Shipment #'),
                'index'     => 'increment_id',
                'filter_index'     => 'main_table.increment_id',
                'type'      => 'text',
            ));
    
            $this->addColumn('created_at', array(
                'header'    => Mage::helper('sales')->__('Date Shipped'),
                'index'     => 'created_at',
                'type'      => 'datetime',
            ));
    
            $this->addColumn('order_increment_id', array(
                'header'    => Mage::helper('sales')->__('Order #'),
                'index'     => 'vendor_increment_id',
                'filter_index'     => 'smo.increment_id',
                'type'      => 'text',
            ));
    
            $this->addColumn('order_created_at', array(
                'header'    => Mage::helper('sales')->__('Order Date'),
                'index'     => 'order_created_at',
                'type'      => 'datetime',
            ));
    
            $this->addColumn('shipping_name', array(
                'header' => Mage::helper('sales')->__('Ship to Name'),
                'index' => 'shipping_name',
            ));
    
            $this->addColumn('total_qty', array(
                'header' => Mage::helper('sales')->__('Total Qty'),
                'index' => 'total_qty',
                'type'  => 'number',
            ));
    
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('sales')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('sales')->__('View'),
                            'url'     => array('base'=>'*/vendors_order_shipment/view'),
                            'field'   => 'shipment_id',
                            'params'  => array('come_from' => 'shipment'),                            
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'is_system' => true
            ));
    
            $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
            $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));
            $this->sortColumnsByOrder();
            return $this;
        } else {
            $this->sortColumnsByOrder();
            return parent::_prepareColumns();
        }
    }

    /**
     * Get url for row
     *
     * @param string $row
     * @return string
     */
    public function getRowUrl($row)
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('smvendors/vendors_orders/actions/ship')) {
            return false;
        }

        return $this->getUrl('*/vendors_order_shipment/view',
            array(
                'shipment_id'=> $row->getId(),
                'come_from' => 'shipment'
            )
        );
    }

    /**
     * Prepare and set options for massaction
     *
     * @return Mage_Adminhtml_Block_Sales_Shipment_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('shipment_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()->addItem('pdfshipments_order', array(
             'label'=> Mage::helper('sales')->__('PDF Packingslips'),
             'url'  => $this->getUrl('*/vendors_order_shipment/pdfshipments'),
        ));

        $this->getMassactionBlock()->addItem('print_shipping_label', array(
             'label'=> Mage::helper('sales')->__('Print Shipping Labels'),
             'url'  => $this->getUrl('*/vendors_order_shipment/massPrintShippingLabel'),
        ));

        return $this;
    }
}
