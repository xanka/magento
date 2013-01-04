<?php

class SM_Vendors_Block_Override_Adminhtml_Report_Sales_Bestsellers_Grid extends Mage_Adminhtml_Block_Report_Sales_Bestsellers_Grid
{
    protected $_columnGroupBy = 'period';

    public function __construct()
    {
        parent::__construct();
        $this->setCountTotals(true);
    }

    public function getResourceCollectionName()
    {
        return 'smvendors/reports_sales_product_bestsellers_collection';
    }

    protected function _prepareColumns()
    {
        $this->addColumn('period', array(
            'header'        => Mage::helper('sales')->__('Period'),
            'index'         => 'period',
            'width'         => 100,
            'sortable'      => false,
            'period_type'   => $this->getPeriodType(),
            'renderer'      => 'adminhtml/report_sales_grid_column_renderer_date',
            'totals_label'  => Mage::helper('adminhtml')->__('Total'),
            'html_decorators' => array('nobr'),
        ));

        $this->addColumn('product_name', array(
            'header'    => Mage::helper('sales')->__('Product Name'),
            'index'     => 'product_name',
            'type'      => 'string',
            'sortable'  => false
        ));

        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }
        $currencyCode = $this->getCurrentCurrencyCode();

        $this->addColumn('product_price', array(
            'header'        => Mage::helper('sales')->__('Price'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'product_price',
            'sortable'      => false
        ));

        $this->addColumn('vendor_name', array(
            'header'        => Mage::helper('sales')->__('Vendor'),
            'index'         => 'vendor_name',
            'type'      => 'string',
            'sortable'      => false
        ));

        $this->addColumn('qty_ordered', array(
            'header'    => Mage::helper('sales')->__('Quantity Ordered'),
            'index'     => 'qty_ordered',
            'type'      => 'number',
            'total'     => 'sum',
            'sortable'  => false
        ));


        $this->addExportType('*/*/exportBestsellersCsv', Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType('*/*/exportBestsellersExcel', Mage::helper('adminhtml')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Get allowed vendor ids array intersected with selected scope in store switcher
     *
     * @return  array
     */
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
        return $vendorIds;
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

        $vendorIds = $this->_getVendorIds();
        //print_r($vendorIds);die;
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
