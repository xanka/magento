<?php
class SM_Dropship_Model_Mysql4_Shipping_Flatrate extends Mage_Core_Model_Mysql4_Abstract {
	public function _construct() {
        $this->_init('smdropship/vendor_shippping_flatrate', 'config_id');
    }
    
}