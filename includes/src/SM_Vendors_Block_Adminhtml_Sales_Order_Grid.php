<?php
class SM_Vendors_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    protected function _prepareCollection()
    {
        if($vendor = Mage::helper('smvendors')->getVendorLogin()) {
            $grandParent = get_parent_class(get_parent_class($this));
            $collection = Mage::getResourceModel($this->_getCollectionClass());
            $collection->join(
                        array('smo' => 'smvendors/order'), 
                        'main_table.entity_id = smo.order_id AND smo.vendor_id = ' . $vendor->getId(),
                        array('vendor_increment_id' => 'smo.increment_id',
                              'vendor_grand_total' => 'smo.grand_total',
                              'vendor_status' => 'smo.status')
                      );
            Mage::dispatchEvent('smvendors_sales_order_grid_prepare_collection', array('order_collection' => $collection));
            $this->setCollection($collection);
            return call_user_func(array($grandParent, '_prepareCollection'));
        } else {
            return parent::_prepareCollection();
        }
        
    }

    protected function _prepareColumns()
    {
        if(Mage::helper('smvendors')->getVendorLogin()) {
            $this->addColumn('real_order_id', array(
                    'header'=> Mage::helper('sales')->__('Order #'),
                    'width' => '120px',
                    'type'  => 'text',
                    'index' => 'vendor_increment_id',
                    'filter_index' => 'smo.increment_id', 
            ));
            
            if (!Mage::app()->isSingleStoreMode()) {
                $this->addColumn('store_id', array(
                        'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                        'index'     => 'store_id',
                        'type'      => 'store',
                        'store_view'=> true,
                        'display_deleted' => true,
                ));
            }
            
            $this->addColumn('created_at', array(
                    'header' => Mage::helper('sales')->__('Purchased On'),
                    'index' => 'created_at',
                    'type' => 'datetime',
                    'width' => '100px',
            ));
            
            $this->addColumn('billing_name', array(
                    'header' => Mage::helper('sales')->__('Bill to Name'),
                    'index' => 'billing_name',
            ));
            
            $this->addColumn('shipping_name', array(
                    'header' => Mage::helper('sales')->__('Ship to Name'),
                    'index' => 'shipping_name',
            ));
            
            $this->addColumn('base_grand_total', array(
                    'header' => Mage::helper('sales')->__('G.T. (Base)'),
                    'index' => 'vendor_grand_total',
                    'type'  => 'currency',
                    'currency' => 'base_currency_code',
            ));
            
            $this->addColumn('grand_total', array(
                    'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
                    'index' => 'vendor_grand_total',
                    'type'  => 'currency',
                    'currency' => 'order_currency_code',
            ));
            
            $this->addColumn('status', array(
                    'header' => Mage::helper('sales')->__('Status'),
                    'index' => 'vendor_status',
            		'filter_index' => 'smo.status',
                    'type'  => 'options',
                    'width' => '70px',
                    'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
            ));
            
            if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
                $this->addColumn('action',
                        array(
                                'header'    => Mage::helper('sales')->__('Action'),
                                'width'     => '50px',
                                'type'      => 'action',
                                'getter'     => 'getId',
                                'actions'   => array(
                                        array(
                                                'caption' => Mage::helper('sales')->__('View'),
                                                'url'     => array('base'=>'*/vendors_order/view'),
                                                'field'   => 'order_id'
                                        )
                                ),
                                'filter'    => false,
                                'sortable'  => false,
                                'index'     => 'stores',
                                'is_system' => true,
                        ));
            }
            //$this->addRssList('rss/order/new', Mage::helper('sales')->__('New Order RSS'));
            
            Mage::dispatchEvent('smvendors_sales_order_grid_prepare_columns', array('order_grid' => $this));
            
            $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
            $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));
            $this->sortColumnsByOrder();
            return $this;            
        } else {
            return parent::_prepareColumns();
        }
    }

    protected function _prepareMassaction()
    {
        if(Mage::helper('smvendors')->getVendorLogin()) {
            $this->setMassactionIdField('entity_id');
            $this->getMassactionBlock()->setFormFieldName('order_ids');
            $this->getMassactionBlock()->setUseSelectAll(false);
            
            if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/cancel')) {
                $this->getMassactionBlock()->addItem('cancel_order', array(
                        'label'=> Mage::helper('sales')->__('Cancel'),
                        'url'  => $this->getUrl('*/sales_order/massCancel'),
                ));
            }
            
//             if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/hold')) {
//                 $this->getMassactionBlock()->addItem('hold_order', array(
//                         'label'=> Mage::helper('sales')->__('Hold'),
//                         'url'  => $this->getUrl('*/sales_order/massHold'),
//                 ));
//             }
            
//             if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/unhold')) {
//                 $this->getMassactionBlock()->addItem('unhold_order', array(
//                         'label'=> Mage::helper('sales')->__('Unhold'),
//                         'url'  => $this->getUrl('*/sales_order/massUnhold'),
//                 ));
//             }
            
            $this->getMassactionBlock()->addItem('pdfinvoices_order', array(
                    'label'=> Mage::helper('sales')->__('Print Invoices'),
                    'url'  => $this->getUrl('*/vendors_order/pdfinvoices'),
            ));
            
            $this->getMassactionBlock()->addItem('pdfshipments_order', array(
                    'label'=> Mage::helper('sales')->__('Print Packingslips'),
                    'url'  => $this->getUrl('*/vendors_order/pdfshipments'),
            ));
            
            $this->getMassactionBlock()->addItem('pdfcreditmemos_order', array(
                    'label'=> Mage::helper('sales')->__('Print Credit Memos'),
                    'url'  => $this->getUrl('*/vendors_order/pdfcreditmemos'),
            ));
            
            $this->getMassactionBlock()->addItem('pdfdocs_order', array(
                    'label'=> Mage::helper('sales')->__('Print All'),
                    'url'  => $this->getUrl('*/vendors_order/pdfdocs'),
            ));
            
            $this->getMassactionBlock()->addItem('print_shipping_label', array(
                    'label'=> Mage::helper('sales')->__('Print Shipping Labels'),
                    'url'  => $this->getUrl('*/vendors_order_shipment/massPrintShippingLabel'),
            ));            
        } else {
            parent::_prepareMassaction();
        }

        return $this;
    }
    
    public function getRowUrl($row)
    {
        if(Mage::helper('smvendors')->getVendorLogin()) { 
            if (Mage::getSingleton('admin/session')->isAllowed('smvendors/vendors_orders/actions/view')) {
                return $this->getUrl('*/vendors_order/view', array('order_id' => $row->getId()));
            }
            return false;
        } else {
            return parent::getRowUrl($row);
        }
    }    
}
