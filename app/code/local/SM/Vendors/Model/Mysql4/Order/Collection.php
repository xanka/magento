<?php
class SM_Vendors_Model_Mysql4_Order_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('smvendors/order');
    }
}