<?php
/**
 * @category    SM
 * @package     SM_Sales
 * @copyright   Copyright (c) 2012 SmartOSC (www.smartosc.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SM_Sales_Helper_Order extends Mage_Customer_Helper_Data
{
	public function getCurrentVendorOrder()
	{
		return Mage::registry('current_vendor_order');
	}
}
