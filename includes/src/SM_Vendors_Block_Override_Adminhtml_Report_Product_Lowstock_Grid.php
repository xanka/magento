<?php

class SM_Vendors_Block_Override_Adminhtml_Report_Product_Lowstock_Grid extends Mage_Adminhtml_Block_Report_Product_Lowstock_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('lowstockGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setVarNameFilter('lowstock_filter');

    }


    protected function _prepareCollection()
    {
        if ($this->getRequest()->getParam('website')) {
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else if ($this->getRequest()->getParam('group')) {
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else if ($this->getRequest()->getParam('store')) {
            $storeId = (int)$this->getRequest()->getParam('store');
        } else {
            $storeId = '';
        }

        /** @var $collection Mage_Reports_Model_Resource_Product_Lowstock_Collection  */
        $collection = Mage::getResourceModel('reports/product_lowstock_collection')
            ->addAttributeToSelect('*')
            ->setStoreId($storeId)
            ->filterByIsQtyProductTypes()
            ->joinInventoryItem('qty')
            ->useManageStockFilter($storeId)
            ->useNotifyStockQtyFilter($storeId)
            ->setOrder('qty', Varien_Data_Collection::SORT_ORDER_ASC);

        if($vendor= Mage::helper('smvendors')->getVendorLogin()){
            $collection->addAttributeToFilter('sm_product_vendor_id',$vendor->getId());
        }
        else{
            $filter = $this->getRequest()->getParam('lowstock_filter');
            $filter = base64_decode($filter);
            parse_str($filter, $output);
            if(!empty($output['sm_product_vendor_id'])){
                $collection->addAttributeToFilter('sm_product_vendor_id',$output['sm_product_vendor_id']);
            }
        }

        if( $storeId ) {
            $collection->addStoreFilter($storeId);
        }
        $this->setCollection($collection);
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    =>Mage::helper('reports')->__('Product Name'),
            'sortable'  =>false,
            'index'     =>'name'
        ));

        $this->addColumn('sm_product_vendor_id',
            array(
                'header'=> Mage::helper('smvendors')->__('Vendor'),
                'width' => '60px',
                'index' => 'sm_product_vendor_id',
                'type'  => 'options',
                'options' => Mage::getResourceModel('smvendors/vendor_collection')->getOptionArray(),
            ));

        $this->addColumn('sku', array(
            'header'    =>Mage::helper('reports')->__('Product SKU'),
            'sortable'  =>false,
            'index'     =>'sku'
        ));

        $this->addColumn('qty', array(
            'header'    =>Mage::helper('reports')->__('Stock Qty'),
            'width'     =>'215px',
            'align'     =>'right',
            'sortable'  =>false,
            'filter'    =>'adminhtml/widget_grid_column_filter_range',
            'index'     =>'qty',
            'type'      =>'number'
        ));

        $this->addExportType('*/*/exportLowstockCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportLowstockExcel', Mage::helper('reports')->__('Excel XML'));

        return $this;
    }

}
