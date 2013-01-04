<?php
class SM_Vendors_Block_Override_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View
{

    public function __construct()
    {
        parent::__construct();
        
        $this->_removeButton('order_cancel');
        $this->_removeButton('order_hold');
        $this->_removeButton('order_unhold');
        $this->_removeButton('order_invoice');
        $this->_removeButton('order_ship');
        $this->_removeButton('order_creditmemo');
    }
}
