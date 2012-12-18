<?php

class SM_Vendors_Block_Override_Adminhtml_Report_Product_Viewed_Grid  extends Mage_Adminhtml_Block_Report_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('gridViewedProducts');
        $this->setTemplate('sm/vendors/reports/grid.phtml');
    }

    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        $this->getCollection()->initReport('smvendors/reports_product_viewed_collection');
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    =>Mage::helper('reports')->__('Product Name'),
            'index'     =>'name',
            'total'     =>Mage::helper('reports')->__('Subtotal')
        ));

        $this->addColumn('price', array(
            'header'    =>Mage::helper('reports')->__('Price'),
            'width'     =>'120px',
            'type'      =>'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'index'     =>'price'
        ));

        $this->addColumn('views', array(
            'header'    =>Mage::helper('reports')->__('Number of Views'),
            'width'     =>'120px',
            'align'     =>'right',
            'index'     =>'views',
            'total'     =>'sum'
        ));

        $this->addColumn('vendor_name', array(
            'header'    =>Mage::helper('reports')->__('Vendor Name'),
            'index'     =>'vendor_name',
            'total'     =>Mage::helper('reports')->__('Subtotal')
        ));

        $this->addExportType('*/*/exportViewedCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportViewedExcel', Mage::helper('reports')->__('Excel XML'));

        return parent::_prepareColumns();
    }

}
