<?php
/**
 * @category    SM
 * @package     SM_Vendors
 * @copyright   Copyright (c) 2012 SmartOSC (www.smartosc.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SM_Vendors_Block_Catalog_Product_List_New extends SM_Vendors_Block_Catalog_Product_List_Base
{	
	/**
	 * (non-PHPdoc)
	 * @see SM_Vendors_Catalog_Block_Product_List_Base::_construct()
	 */
	protected function _construct()
	{
		parent::_construct();
		
		$this->addData(array(
				'cache_lifetime' => 300,
				'cache_tags' => array(Mage_Catalog_Model_Product::CACHE_TAG),
		));
	}

	/**
	 * (non-PHPdoc)
	 * @see SM_Vendors_Catalog_Block_Product_List_Base::_getProductCollection()
	 */
	protected function _getProductCollection()
	{
		if (is_null($this->_productCollection)) {
			$collection = Mage::getResourceModel('catalog/product_collection');
				
			Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
			Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

			$collection = $this->_addProductAttributesAndPrices($collection)
				->addStoreFilter()
				->addAttributeToSelect('sm_product_vendor_id')
				->addAttributeToFilter('sm_product_vendor_id', $this->getVendor()->getId());
			
			if ($this->helper('smvendors')->showNewArrival()) {
				$collection
					->addAttributeToFilter('news_from_date', array('date' => true, 'to' => date('Y-m-d 23:59:59')))
					->addAttributeToFilter('news_to_date', array('or'=> array(
							0 => array('date' => true, 'from' => date('Y-m-d 00:00:00')),
							1 => array('is' => new Zend_Db_Expr('null')))
					), 'left')
					->addAttributeToSort('news_from_date', 'desc');
			}

			$this->_productCollection = $collection;
		}

		return $this->_productCollection;
	}

	/**
	 *
	 * @return array
	 */
	public function getCacheKeyInfo()
	{
		return array(
				'SMVENDORS_PRODUCT_VENDOR',
				Mage::app()->getStore()->getId(),
				Mage::getDesign()->getPackageName(),
				Mage::getDesign()->getTheme('template'),
				Mage::getSingleton('customer/session')->getCustomerGroupId(),
				'template' => $this->getTemplate(),
				$this->getPageSize()
		);
	}
}