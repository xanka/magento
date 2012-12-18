<?php
class SM_Vendors_Block_Adminhtml_Sales_Shipment extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_blockGroup = 'smvendors';
        $this->_controller = 'adminhtml_sales_shipment';
        $this->_headerText = Mage::helper('sales')->__('Shipments');
        parent::__construct();
        $this->_removeButton('add');
    }
}
