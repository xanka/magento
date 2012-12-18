<?php
class SM_Vendors_Model_Mysql4_Page extends Mage_Core_Model_Mysql4_Abstract {
	public function _construct() {
        $this->_init('smvendors/page', 'id');
    }
}