<?php
/**
 * @category    SM
 * @package     SM_Sales
 * @copyright   Copyright (c) 2012 SmartOSC (www.smartosc.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SM_Sales_Block_Order_Totals extends Mage_Sales_Block_Order_Totals
{
	/**
	 * Associated array of totals
	 * array(
	 *  $totalCode => $totalObject
	 * )
	 *
	 * @var array
	 */
	protected $_vendorOrder = null;
	
	/**
	 * 
	 * @return SM_Vendors_Model_Order
	 */
	public function getVendorOrder()
	{
		if ($this->_vendorOrder === null) {
			if ($this->hasData('vendor_order')) {
				$this->_vendorOrder = $this->_getData('vendor_order');
			} elseif (Mage::registry('current_vendor_order')) {
				$this->_vendorOrder = Mage::registry('current_vendor_order');
			} elseif ($this->getParentBlock()->getVendorOrder()) {
				$this->_vendorOrder = $this->getParentBlock()->getVendorOrder();
			}
		}
		return $this->_vendorOrder;
	}

	public function setVendorOrder($vendorOrder)
	{
		$this->_vendorOrder = $vendorOrder;
		return $this;
	}

	/**
	 * Get totals source object
	 *
	 * @return Mage_Sales_Model_Order
	 */
	public function getSource()
	{
		return $this->getVendorOrder();
	}
}
