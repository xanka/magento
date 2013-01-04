<?php
/**
 * @category    SM
 * @package     SM_Vendors
 * @copyright   Copyright (c) 2012 SmartOSC
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SM_Vendors_Model_Mysql4_Customer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract 
{
	/**
	 * (non-PHPdoc)
	 * @see Mage_Core_Model_Resource_Db_Collection_Abstract::_construct()
	 */
	public function _construct() 
	{
		parent::_construct();
		$this->_init('smvendors/customer');
	}
	
	/**
	 * 
	 * @param int $vendor_id
	 * @param int $customer_id
	 * @return SM_Vendors_Model_Mysql4_Customer_Collection
	 */
	public function addVendorCustomerFilter($vendor_id, $customer_id)
	{
		$this->addFieldToFilter('vendor_id', $vendor_id)
			->addFieldToFilter('customer_id', $customer_id);
		
		return $this;
	}
}