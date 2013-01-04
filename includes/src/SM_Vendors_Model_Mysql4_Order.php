<?php
class SM_Vendors_Model_Mysql4_Order extends Mage_Core_Model_Mysql4_Abstract {
    
	public function _construct() {
        $this->_init('smvendors/order', 'entity_id');
    }
    
    public function getByOriginOrderId($orderId, $vendorId)
    {
        $select = $this->_getReadAdapter()->select()
        ->from($this->getTable('smvendors/order'))
        ->where('order_id = ?', (int)$orderId)
        ->where('vendor_id = ?', (int)$vendorId)
        ->limit(1);
        $result = $this->_getReadAdapter()->fetchRow($select);
        return $result;        
    }
}