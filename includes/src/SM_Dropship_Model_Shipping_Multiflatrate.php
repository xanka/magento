<?php
class SM_Dropship_Model_Shipping_Multiflatrate extends Mage_Core_Model_Abstract {
	public function _construct() {
        parent::_construct();
        $this->_init('smdropship/shipping_multiflatrate');
    }

    public function getVendorIds(){
    	$vendorIds = explode(',', $this->getData('vendor_ids'));
    	$result = array();
    	foreach($vendorIds as $vendorId){
    		if(is_numeric($vendorId)){
    			$result[$vendorId] = $vendorId;
    		}
    	}
    	return $result;
    }
}