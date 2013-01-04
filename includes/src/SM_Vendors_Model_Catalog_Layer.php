<?php
/**
 * @category    SM
 * @package     SM_Vendors
 * @copyright   Copyright (c) 2012 SmartOSC (www.smartosc.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SM_Vendors_Model_Catalog_Layer extends Mage_Catalog_Model_Layer
{
	/**
	 * 
	 */
	protected function _getStoreId()
	{
		$storeId = Mage::app()->getStore()->getId();
		return $storeId;
	}

	/**
	 * 
	 */
	protected function _getCustomerGroupId()
	{
		$custGroupID = null;
		if($custGroupID == null) {
			$custGroupID = Mage::getSingleton('customer/session')->getCustomerGroupId();
		}
		return $custGroupID;
	}
	public function getProductCollection()
	{
		if (is_null($this->_productCollection)) {
			$storeId = $this->_getStoreId();
			$websiteId = Mage::app()->getStore($storeId)->getWebsiteId();

			$collection = Mage::getModel('catalog/product')->getCollection();
			if($this->getVendor()){
				$collection->addAttributeToSelect('sm_product_vendor_id')
				->addAttributeToFilter('sm_product_vendor_id',$this->getVendor()->getId());
			}
			Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
			Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

			$this->prepareProductCollection($collection);

			$this->_productCollection = $collection;
		}
		return $this->_productCollection;
	}

	public function getVendor()
	{
        if (!is_null(Mage::registry('current_vendor'))) {
            return Mage::registry('current_vendor');
        }
        return Mage::getSingleton('core/session')->getData('current_vendor');

	}
}

