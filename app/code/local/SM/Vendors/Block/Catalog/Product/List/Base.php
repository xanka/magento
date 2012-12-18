<?php

/**
 * @category    SM
 * @package     SM_Vendors
 * @copyright   Copyright (c) 2012 SmartOSC (www.smartosc.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SM_Vendors_Block_Catalog_Product_List_Base extends Mage_Catalog_Block_Product_List {

    const DEFAULT_PAGE_SIZE = 5;

    /**
     *
     * @var int
     */
    protected $_pageSize = null;

    /**
     *
     * var SM_Vendors_Model_Vendor
     */
    protected $_currentVendor = null;

    /**
     * (non-PHPdoc)
     * @see Mage_Catalog_Block_Product_List::_getProductCollection()
     */
    protected function _getProductCollection() {
        $productCollection = parent::_getProductCollection();

        $productCollection
                ->addAttributeToSelect('sm_product_vendor_id')
                ->addFieldToFilter('sm_product_vendor_id', $this->getVendor()->getId());

        return $productCollection;
    }

    /**
     * get current vendor
     *
     * @return SM_Vendors_Model_Vendor
     */
    public function getVendor() {
        if (is_null($this->_currentVendor)) {
            $this->_currentVendor = Mage::registry('current_vendor');
        }

        return $this->_currentVendor;
    }

    /**
     * @param int $pageSize
     * @return SM_Vendors_Block_Catalog_Product_New
     */
    public function setPageSize($pageSize) {
        $this->_pageSize = (int) $pageSize;

        return $this;
    }

    /**
     *
     * @return int
     */
    public function getPageSize() {
        if (is_null($this->_pageSize)) {
            $this->_pageSize = self::DEFAULT_PAGE_SIZE;
        }
        return $this->_pageSize;
    }

}
