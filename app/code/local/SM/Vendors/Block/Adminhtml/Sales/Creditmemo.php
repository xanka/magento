<?php
class SM_Vendors_Block_Adminhtml_Sales_Creditmemo extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_blockGroup = 'smvendors';
        $this->_controller = 'adminhtml_sales_creditmemo';
        $this->_headerText = Mage::helper('sales')->__('Credit Memos');
        parent::__construct();
        $this->_removeButton('add');
    }
}
