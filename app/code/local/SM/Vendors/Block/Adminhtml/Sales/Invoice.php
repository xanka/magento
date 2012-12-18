<?php
class SM_Vendors_Block_Adminhtml_Sales_Invoice extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'smvendors';
        $this->_controller = 'adminhtml_sales_invoice';
        $this->_headerText = Mage::helper('sales')->__('Invoices');
        parent::__construct();
        $this->_removeButton('add');
    }

    public function getPaymentInfoHtml()
    {
        return $this->getChildHtml('payment_info');
    }
}
