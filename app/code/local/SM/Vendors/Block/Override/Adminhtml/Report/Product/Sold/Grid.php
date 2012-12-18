<?php

class SM_Vendors_Block_Override_Adminhtml_Report_Product_Sold_Grid extends Mage_Adminhtml_Block_Report_Product_Sold_Grid
{


    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sm/vendors/reports/grid.phtml');
    }

    /**
     * Prepare collection object for grid
     *
     * @return Mage_Adminhtml_Block_Report_Product_Sold_Grid
     */
    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        $this->getCollection()
            ->initReport('smvendors/reports_product_sold_collection');
        return $this;
    }

    /**
     * Prepare Grid columns
     *
     * @return Mage_Adminhtml_Block_Report_Product_Sold_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    =>Mage::helper('reports')->__('Product Name'),
            'index'     =>'order_items_name'
        ));



        $this->addColumn('ordered_qty', array(
            'header'    =>Mage::helper('reports')->__('Quantity Ordered'),
            'width'     =>'120px',
            'align'     =>'right',
            'index'     =>'ordered_qty',
            'total'     =>'sum',
            'type'      =>'number'
        ));

        $this->addColumn('vendor_name', array(
            'header'    =>Mage::helper('reports')->__('Vendor'),
            'index'     =>'vendor_name'
        ));

        $this->addExportType('*/*/exportSoldCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportSoldExcel', Mage::helper('reports')->__('Excel XML'));

        return parent::_prepareColumns();
    }
}
