<?php
class SM_Reviews_Model_Reviews extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('smreviews/reviews');
    }

    public function getAverageRatingForVendor($vendorId, $decimal = 0)
    {
    	$avgRating = $this->getResource()->getAverageRatingForVendor($vendorId);
    	if($avgRating) {
    		return round(floatval($avgRating), $decimal);
    	} else {
    		return 0;
    	}
    }
    
    public function loadByCustomerVendorOrder($customerId, $vendorId, $orderId)
    {
    	$review = $this->getResource()->loadByCustomerVendorOrder($customerId, $vendorId, $orderId);
    	if(!empty($review)) {
    		$this->addData($review);
    	}
    	
    	return $this;
    }
}
