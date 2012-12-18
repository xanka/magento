<?php
/**
 * @category    SM
 * @package     SM_Vendors
 * @copyright   Copyright (c) 2012 SmartOSC (www.smartosc.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SM_Vendors_Block_Page_Tabs extends Mage_Core_Block_Template
{
	/**
	 * 
	 * @var SM_Vendors_Model_Vendor
	 */
	protected $_currentVendor = null;
	
	/**
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * get all vendor pages
	 */
	public function getAllPages()
	{
		$page = null;
		if ($vendor = $this->getCurrentVendor()) {
			$pages = Mage::getResourceModel('smvendors/page_collection')->addVendorToFilter($vendor->getId());
		}
		return $pages;
	}
	
	/**
	 * Get current vendor object
	 * 
	 * @return SM_Vendors_Model_Vendor
	 */
	public function getCurrentVendor()
	{
		if (is_null($this->_currentVendor)) {
			$this->_currentVendor = Mage::registry('current_vendor');
		}
		
		return $this->_currentVendor;
	}
}