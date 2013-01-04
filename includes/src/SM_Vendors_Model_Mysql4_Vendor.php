<?php
class SM_Vendors_Model_Mysql4_Vendor extends Mage_Core_Model_Mysql4_Abstract {

    
	public function _construct() {
        $this->_init('smvendors/vendor', 'vendor_id');
    }
	
	public function getByCustomerId($customer_id){
			$select = $this->_getReadAdapter()->select()
                ->from($this->getTable('vendor'))
                ->where('customer_id=?', (int)$customer_id)
				->limit(1);
			$vendor = $this->_getReadAdapter()->fetchRow($select);
			return $vendor;
	}
	
	public function getByUserId($user_id){
		$select = $this->_getReadAdapter()->select()
			->from($this->getTable('vendor'))
			->where('user_id=?', (int)$user_id)
			->limit(1);
		$vendor = $this->_getReadAdapter()->fetchRow($select);
		return $vendor;
	}
	
	public function _saveVendorPrefix($object){
		if($object->getId() && $object->getCustomerId()){
			$write = $this->_getWriteAdapter();
			$vendorPrefix = Mage::helper('smvendors')->getPrefixFromId($object->getCustomerId());
			$write->update($this->getTable('smvendors/vendor'), 
			                array('vendor_prefix'=>$vendorPrefix),
			                'vendor_id='.$object->getId());
		}
	}
}