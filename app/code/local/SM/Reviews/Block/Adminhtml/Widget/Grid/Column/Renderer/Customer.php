<?php
class SM_Reviews_Block_Adminhtml_Widget_Grid_Column_Renderer_Customer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function __construct() {

    }

    public function render(Varien_Object $row) {
        return $this->_getValue($row);
    }

    protected function _getValue(Varien_Object $row) {
        $val = $row->getData($this->getColumn()->getIndex());
        $_order = Mage::getModel('sales/order')->load($val);
        $_customer = Mage::getModel('customer/customer')->load($_order->getCustomerId());
        
        return $_customer->getEmail();
    }
}