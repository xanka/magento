<?php
class SM_Vendors_Block_Adminhtml_Sales_Creditmemo_Grid extends Mage_Adminhtml_Block_Sales_Creditmemo_Grid
{
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


    protected function _prepareColumns()
    {
        if($vendor = Mage::helper('smvendors')->getVendorLogin()) {
            $this->addColumn('increment_id', array(
                'header'    => Mage::helper('sales')->__('Credit Memo #'),
                'index'     => 'increment_id',
                'filter_index'     => 'main_table.increment_id',
                'type'      => 'text',
            ));
    
            $this->addColumn('created_at', array(
                'header'    => Mage::helper('sales')->__('Created At'),
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
    
            $this->addColumn('billing_name', array(
                'header' => Mage::helper('sales')->__('Bill to Name'),
                'index' => 'billing_name',
            ));
    
            $this->addColumn('state', array(
                'header'    => Mage::helper('sales')->__('Status'),
                'index'     => 'state',
                'type'      => 'options',
                'options'   => Mage::getModel('sales/order_creditmemo')->getStates(),
            ));
    
            $this->addColumn('grand_total', array(
                'header'    => Mage::helper('customer')->__('Refunded'),
                'index'     => 'grand_total',
                'type'      => 'currency',
                'align'     => 'right',
                'currency'  => 'order_currency_code',
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
                            'url'     => array('base'=>'*/vendors_order_creditmemo/view'),
                            'field'   => 'creditmemo_id',
                            'params'  => array('come_from' => 'sales_creditmemo'),
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
            return parent::_prepareColumns();
        }
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('creditmemo_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()->addItem('pdfcreditmemos_order', array(
             'label'=> Mage::helper('sales')->__('PDF Credit Memos'),
             'url'  => $this->getUrl('*/vendors_order_creditmemo/pdfcreditmemos'),
        ));

        return $this;
    }

    public function getRowUrl($row)
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('smvendors/vendors_orders/actions/creditmemo')) {
            return false;
        }

        return $this->getUrl('*/vendors_order_creditmemo/view',
            array(
                'creditmemo_id'=> $row->getId(),
                'come_from' => 'sales_creditmemo'
            )
        );
    }
}
