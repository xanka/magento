<?php
class SM_Dropship_Model_Shipping_Flatrate extends Mage_Core_Model_Abstract {
	public function _construct() {
        parent::_construct();
        $this->_init('smdropship/shipping_flatrate');
    }
    
	public function load($id, $field=null){
		return $post = parent::load($id, $field);
    }
}