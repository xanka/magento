<?php
/**
 * @category    SM
 * @package     SM_Vendors
 * @copyright   Copyright (c) 2012 SmartOSC (www.smartosc.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SM_Vendors_Block_Catalog_Layer_View extends Mage_Catalog_Block_Layer_View
{
	/**
	 * (non-PHPdoc)
	 * @see Mage_Catalog_Block_Layer_View::getLayer()
	 */
	public function getLayer()
	{
		return Mage::getSingleton('smvendors/catalog_layer');
	}
}