<?php

class SM_Vendors_Block_Adminhtml_Reports_VendorOrders_Grid extends Mage_Adminhtml_Block_Report_Grid_Abstract {

    protected $_columnGroupBy = 'period';

    public function __construct() {
        parent::__construct();
        if (Mage::helper('smvendors')->getVendorLogin())
            $this->setCountTotals(false);
        else
            $this->setCountTotals(true);


//        $this->setCountSubTotals(true);
        //$this->setTemplate('report/grid.phtml');
    }

    public function getResourceCollectionName() {
        return ($this->getFilterData()->getData('report_type') == 'created_at_order') ? 'smvendors/reports_vendorOrders_createdAtCollection' : 'smvendors/reports_vendorOrders_updatedAtCollection';
    }

    protected function _prepareColumns() {

        $this->addColumn('period', array(
            'header' => Mage::helper('sales')->__('Date Ordered'),
            'index' => 'period',
            'width' => 100,
            'sortable' => false,
            'period_type' => $this->getPeriodType(),
            'renderer' => 'adminhtml/report_sales_grid_column_renderer_date',
            'totals_label' => Mage::helper('sales')->__('Total'),
            'subtotals_label' => Mage::helper('sales')->__('Subtotal'),
            'html_decorators' => array('nobr'),
        ));

        $this->addColumn('vendor_prefix', array(
            'header' => Mage::helper('smvendors')->__('Prefix'),
            'sortable' => false,
            'index' => 'vendor_prefix',
            'totals_label' => '',
            'subtotals_label' => ''
        ));

        $this->addColumn('vendor_name', array(
            'header' => Mage::helper('smvendors')->__('Vendor name'),
            'sortable' => false,
            'index' => 'vendor_name',
            'totals_label' => '',
            'subtotals_label' => ''
        ));

        $this->addColumn('order_id', array(
            'header' => Mage::helper('smvendors')->__('Number of Orders'),
            'index' => 'number_order',
            'sortable' => false,
            'type' => 'number'
        ));

        $this->addColumn('store_name', array(
            'header' => Mage::helper('smvendors')->__('Store name'),
            'sortable' => false,
            'index' => 'store_name',
            //'renderer'  => 'smvendors/adminhtml_reports_ordersdelivery_grid_renderer_store',
            'totals_label' => '',
            'subtotals_label' => ''
        ));

        $this->addColumn('tax_amount', array(
            'header' => Mage::helper('smvendors')->__('VAT amount'),
            'sortable' => false,
            'index' => 'tax_amount',
            'type' => 'number'
        ));

        $this->addColumn('shipping_amount', array(
            'header' => Mage::helper('smvendors')->__('Delivery Amount'),
            'sortable' => false,
            'index' => 'shipping_amount',
            'type' => 'number'
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('smvendors')->__('Total Amount'),
            'sortable' => false,
            'index' => 'grand_total',
            'type' => 'number'
        ));

        $this->addColumn('commission_amount', array(
            'header' => Mage::helper('smvendors')->__('Commission'),
            'sortable' => false,
            'index' => 'commission_amount',
            'type' => 'number'
        ));

        $this->addColumn('currency_code', array(
            'header' => Mage::helper('smvendors')->__('Currency'),
            'sortable' => false,
            'index' => 'currency_code',
            'totals_label' => '',
            'subtotals_label' => ''
        ));

        $this->addExportType('*/*/exportVendorOrdersCsv', Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType('*/*/exportVendorOrdersExcel', Mage::helper('adminhtml')->__('Excel'));

        return parent::_prepareColumns();
    }

    protected function getGrandParent() {
        return get_parent_class(get_parent_class($this));
    }

    /**
     * Get allowed vendor ids array intersected with selected scope in store switcher
     *
     * @return  array
     */
    protected function _getVendorIds() {
        $filterData = $this->getFilterData();

        if ($filterData) {
            $vendorIdsData = $filterData->getData('vendors');
            if (!empty($vendorIdsData)) {
                $vendorIds = array($vendorIdsData);
            } else {
                $vendorIds = array();
            }
        } else {
            $vendorIds = array();
        }

        $vendorIds = array_values($vendorIds);
        return $vendorIds;
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    public function setIsExport($is_export = false) {
        $this->_isExport = $is_export;
    }

    protected function _prepareCollection() {
        $filterData = $this->getFilterData();

        if ($filterData->getData('from') == null || $filterData->getData('to') == null) {
            $this->setCountTotals(false);
            $this->setCountSubTotals(false);
            return parent::_prepareCollection();
        }

        $storeIds = $this->_getStoreIds();
        ;

        $orderStatuses = $filterData->getData('order_statuses');
        if (is_array($orderStatuses)) {
            if (count($orderStatuses) == 1 && strpos($orderStatuses[0], ',') !== false) {
                $filterData->setData('order_statuses', explode(',', $orderStatuses[0]));
            }
        }

        $vendorIds = $this->_getVendorIds();
        //print_r($vendorIds);
        $resourceCollection = Mage::getResourceModel($this->getResourceCollectionName())
                ->setPeriod($filterData->getData('period_type'))
                ->setDateRange($filterData->getData('from', null), $filterData->getData('to', null))
                ->addStoreFilter($storeIds)
                ->addVendorFilter($vendorIds)
                ->addOrderStatusFilter($filterData->getData('order_statuses'))
                ->setAggregatedColumns($this->_getAggregatedColumns());

        if ($this->_isExport) {
            $this->setCollection($resourceCollection);
            return $this;
        }

        if ($filterData->getData('show_empty_rows', false)) {
            Mage::helper('reports')->prepareIntervalsCollection(
                    $this->getCollection(), $filterData->getData('from', null), $filterData->getData('to', null), $filterData->getData('period_type')
            );
        }

        if ($this->getCountSubTotals()) {
            $this->getSubTotals();
        }

        if ($this->getCountTotals()) {
            $totalsCollection = Mage::getResourceModel($this->getResourceCollectionName())
                    ->setPeriod($filterData->getData('period_type'))
                    ->setDateRange($filterData->getData('from', null), $filterData->getData('to', null))
                    ->addStoreFilter($storeIds)
                    ->addVendorFilter($vendorIds)
                    ->addOrderStatusFilter($filterData->getData('order_statuses'))
                    ->setAggregatedColumns($this->_getAggregatedColumns())
                    ->isTotals(true);
            foreach ($totalsCollection as $item) {
                $this->setTotals($item);
                break;
            }
        }

        $this->getCollection()->setColumnGroupBy($this->_columnGroupBy);
        $this->getCollection()->setResourceCollection($resourceCollection);
        //$grandParent = $this->getGrandParent();
        return $this;
    }

    function getCountTotals() {


        if (!$this->getTotals()) {
            $filterData = $this->getFilterData();
            $vendorIds = $this->_getVendorIds();

            $totalsCollection = Mage::getResourceModel($this->getResourceCollectionName())
                ->setPeriod($filterData->getData('period_type'))
                ->setDateRange($filterData->getData('from', null), $filterData->getData('to', null))
                ->addVendorFilter($vendorIds)
                ->addStoreFilter($this->_getStoreIds())
                ->setAggregatedColumns($this->_getAggregatedColumns())
                ->isTotals(true);

            $this->_addOrderStatusFilter($totalsCollection, $filterData);

            if (count($totalsCollection->getItems()) < 1 || !$filterData->getData('from')) {
                $this->setTotals(new Varien_Object());
            } else {
                foreach ($totalsCollection->getItems() as $item) {
                    $this->setTotals($item);
                    break;
                }
            }
        }

        return $this->_countTotals;
    }

}

