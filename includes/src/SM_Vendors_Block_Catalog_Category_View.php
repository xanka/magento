<?php
/**
 * @category    SM
 * @package     SM_Vendors
 * @copyright   Copyright (c) 2012 SmartOSC (www.smartosc.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SM_Vendors_Block_Catalog_Category_View extends Mage_Core_Block_Template
{
	/**
	 *
	 * @var Mage_Catalog_Model_Category
	 */
	protected $_currentCategory = null;

	/**
	 *
	 * @var Mage_Catalog_Model_Resource_Product_Collection
	 */
	protected $_productCollection = null;

	/**
	 *
	 * var SM_Vendors_Model_Vendor
	 */
	protected $_currentVendor = null;

	/**
	 * (non-PHPdoc)
	 * @see Mage_Core_Block_Abstract::_prepareLayout()
	 */
	protected function _prepareLayout()
	{
		return parent::_prepareLayout();
	}

	/**
	 *
	 */
	protected function _getProductCollection()
	{
		if (is_null($this->_productCollection)) {
			$this->_productCollection = Mage::getSingleton('smvendors/catalog_layer')->getProductCollection();
		}

		return $this->_productCollection;
	}

	/**
	 *
	 */
	public function setListCollection()
	{
		$productList = $this->getChild('vendor_product_list');
		$productList->setCollection($this->_getProductCollection());
	}

	/**
	 *
	 */
	public function IsRssCatalogEnable()
	{
		$path = Mage_Rss_Block_List::XML_PATH_RSS_METHODS.'/catalog/special';

		return (bool)Mage::getStoreConfig($path);
	}

	/**
	 *
	 */
	public function getRssLink()
	{
		$param = array('sid' => $this->getCurrentStoreId());

		return Mage::getUrl(Mage_Rss_Block_List::XML_PATH_RSS_METHODS.'/catalog/special', $param);
	}

	/**
	 *
	 */
	public function getProductListHtml()
	{
		return $this->getChildHtml('vendor_product_list');
	}

	/**
	 * get current vendor
	 *
	 * @return SM_Vendors_Model_Vendor
	 */
	public function getVendor()
	{
		if (is_null($this->_currentVendor)) {
			$this->_currentVendor = Mage::registry('current_vendor');
		}

		return $this->_currentVendor;
	}

	/**
	 * Retrieve current category model object
	 *
	 * @return Mage_Catalog_Model_Category
	 */
	public function getCurrentCategory()
	{
		if (is_null($this->_currentCategory)) {
			$this->_currentCategory = Mage::registry('current_category');
		}

		return $this->_currentCategory;
	}
}
