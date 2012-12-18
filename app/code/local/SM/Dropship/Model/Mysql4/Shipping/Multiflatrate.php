<?php
class SM_Dropship_Model_Mysql4_Shipping_Multiflatrate extends Mage_Core_Model_Mysql4_Abstract {
	public function _construct() {
        $this->_init('smdropship/vendor_shippping_multi_flat_rate', 'rate_id');
    }
    
}