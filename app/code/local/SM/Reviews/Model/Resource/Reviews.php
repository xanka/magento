<?php

class SM_Reviews_Model_Resource_Reviews extends Mage_Core_Model_Resource_Db_Abstract
{
   
    protected function _construct()
    {
        $this->_init('smreviews/reviews', 'review_id');
    }
    
    public function getAverageRatingForVendor($vendorId)
    {
    	$read = $this->_getReadAdapter();
    	$query = $read->select()->from($this->getTable('reviews'), '')
    		  ->where("vendor_id = ?", (int)$vendorId)
    		  ->where("status = ?", SM_Reviews_Model_Reviews_Status::STATUS_ENABLED)
    		  ->columns("AVG(rating)");
    	return $read->fetchOne($query);
    }
    
    public function loadByCustomerVendorOrder($customerId, $vendorId, $orderId)
    {
    	$read = $this->_getReadAdapter();
    	$read->setFetchMode(Zend_Db::FETCH_ASSOC);
    	$query = $read->select()->from($this->getTable('reviews'))
    		->where("vendor_id = ?", (int)$vendorId)
    		->where("customer_id = ?", (int)$customerId)
    		->where("order_id = ?", (int)$orderId);
    	return $read->fetchRow($query);
    }
}