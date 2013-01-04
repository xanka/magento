<?php
/**
 * @category    SM
 * @package     SM_Vendors
 * @copyright   Copyright (c) 2012 SmartOSC (www.smartosc.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SM_Vendors_Block_Vendor_Featured extends Mage_Core_Block_Template
{
	/**
	 * 
	 * @var unknown_type
	 */
	protected $_vendorCollection = null;
	
	/**
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('sm/vendors/block/featured_vendors.phtml');
	}
	
	/**
	 * 
	 * @return SM_Vendors_Model_Mysql4_Vendor_Collection
	 */
	public function getVendorCollection()
	{
		if (is_null($this->_vendorCollection)) {
			$this->_vendorCollection = Mage::getResourceModel('smvendors/vendor_collection')
				->addFieldToFilter('vendor_feature', 1)
				->addFieldToFilter('vendor_status', 1);
		}
		
		return $this->_vendorCollection;
	}
}