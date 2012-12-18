<?php

class SM_Vendors_Block_Override_Adminhtml_Report_Sales_Shipping_Grid extends Mage_Adminhtml_Block_Report_Sales_Shipping_Grid
{
    protected $_columnGroupBy = 'period';

    public function __construct()
    {
        parent::__construct();
        $this->setCountTotals(true);
        $this->setCountSubTotals(true);
    }

    public function getResourceCollectionName()
    {
        return ($this->getFilterData()->getData('report_type') == 'created_at_shipment')
            ? 'sales/report_shipping_collection_shipment'
            : 'smvendors/sales_report_shipping_collection_order';
    }

    protected function _prepareColumns()
    {
        $this->addColumn('period', array(
            'header'            => Mage::helper('sales')->__('Period'),
            'index'             => 'period',
            'width'             => 100,
            'sortable'          => false,
            'period_type'       => $this->getPeriodType(),
            'renderer'          => 'adminhtml/report_sales_grid_column_renderer_date',
            'totals_label'      => Mage::helper('sales')->__('Total'),
            //'subtotals_label'   => Mage::helper('sales')->__('Subtotal'),
            'html_decorators' => array('nobr'),
        ));

        $this->addColumn('shipping_description', array(
            'header'    => Mage::helper('sales')->__('Carrier/Method'),
            'index'     => 'shipping_description',
            'sortable'  => false
        ));


        $this->addColumn('vendor_name', array(
            'header'    => Mage::helper('sales')->__('Vendor Name'),
            'index'     => 'vendor_name',
            'sortable'  => false,
        ));

        $this->addColumn('orders_count', array(
            'header'    => Mage::helper('sales')->__('Number of Orders'),
            'index'     => 'orders_count',
            'total'     => 'sum',
            'type'      => 'number',
            'width'     => 100,
            'sortable'  => false
        ));

        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }

        $this->addColumn('total_shipping', array(
            'header'        => Mage::helper('sales')->__('Total Sales Shipping'),
            'type'          => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'index'         => 'total_shipping',
            'total'         => 'sum',
            'sortable'      => false
        ));

        $this->addColumn('total_shipping_actual', array(
            'header'        => Mage::helper('sales')->__('Total Shipping'),
            'type'          => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'index'         => 'total_shipping_actual',
            'total'         => 'sum',
            'sortable'      => false
        ));

        $this->addExportType('*/*/exportShippingCsv', Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType('*/*/exportShippingExcel', Mage::helper('adminhtml')->__('Excel XML'));

        return $this;
    }

    protected function _prepareCollection()
    {
        $filterData = $this->getFilterData();

        if ($filterData->getData('from') == null || $filterData->getData('to') == null) {
            $this->setCountTotals(false);
            $this->setCountSubTotals(false);
            return parent::_prepareCollection();
        }

        $storeIds = $this->_getStoreIds();;

        $orderStatuses = $filterData->getData('order_statuses');
        if (is_array($orderStatuses)) {
            if (count($orderStatuses) == 1 && strpos($orderStatuses[0],',')!== false) {
                $filterData->setData('order_statuses', explode(',',$orderStatuses[0]));
            }
        }
//        var_dump(get_class(Mage::getResourceModel($this->getResourceCollectionName())));
//        die;
        $vendorIds = $this->_getVendorIds();
        $resourceCollection = Mage::getResourceModel($this->getResourceCollectionName())
            ->setPeriod($filterData->getData('period_type'))
            ->setDateRange($filterData->getData('from', null), $filterData->getData('to', null))
            ->addStoreFilter($storeIds)

            ->addOrderStatusFilter($filterData->getData('order_statuses'))
            ->setAggregatedColumns($this->_getAggregatedColumns());

        if ($this->_isExport) {
            $this->setCollection($resourceCollection);
            return $this;
        }

        if ($filterData->getData('show_empty_rows', false)) {
            Mage::helper('reports')->prepareIntervalsCollection(
                $this->getCollection(),
                $filterData->getData('from', null),
                $filterData->getData('to', null),
                $filterData->getData('period_type')
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

        return parent::_prepareCollection();
    }

    protected function _getVendorIds()
    {
        $filterData = $this->getFilterData();

        if ($filterData) {
            $vendorIdsData = $filterData->getData('vendors');
            if(!empty($vendorIdsData)){
                $vendorIds = array($vendorIdsData);
            }
            else{
                $vendorIds = array();
            }

        } else {
            $vendorIds = array();
        }

        $vendorIds = array_values($vendorIds);

        if (!Mage::registry('current_vendor_report')) {
            Mage::register('current_vendor_report',$vendorIds);
        }
        return $vendorIds;
    }
}



