<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml dashboard most viewed products grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class SM_Vendors_Block_Override_Adminhtml_Dashboard_Tab_Products_Viewed extends Mage_Adminhtml_Block_Dashboard_Tab_Products_Viewed
{

    protected function _prepareCollection()
    {
        if ($this->getParam('website')) {
            $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else if ($this->getParam('group')) {
            $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else {
            $storeId = (int)$this->getParam('store');
        }
        $collection = Mage::getResourceModel('reports/product_collection')
            ->addAttributeToSelect('*')
            ->addViewsCount()
            ->setStoreId($storeId)
            ->addStoreFilter($storeId);
		if($vendor = Mage::helper('smvendors')->getVendorLogin()){
        	$collection->addAttributeToFilter('sm_product_vendor_id',$vendor->getId());
		}  
		
		
		
		$adapter = Mage::getSingleton('core/resource')->getConnection('core_write');
        $product  = Mage::getResourceSingleton('catalog/product');
        $attr     = $product->getAttribute('sm_product_vendor_id');
            $joinExprProductVendorId       = array(
                'product_vendor_id.entity_id = e.entity_id',
                'product_vendor_id.store_id = report_table_views.store_id',
                $adapter->quoteInto('product_vendor_id.entity_type_id = ?', $product->getTypeId()),
                $adapter->quoteInto('product_vendor_id.attribute_id = ?', $attr->getAttributeId())
            );
            $joinExprProductVendorId        = implode(' AND ', $joinExprProductVendorId);
            $joinExprProductDefaultVendorId = array(
                'product_default_vendor_id.entity_id = e.entity_id',
                'product_default_vendor_id.store_id = 0',
                $adapter->quoteInto('product_default_vendor_id.entity_type_id = ?', $product->getTypeId()),
                $adapter->quoteInto('product_default_vendor_id.attribute_id = ?', $attr->getAttributeId())
            );
            $joinExprProductDefaultVendorId = implode(' AND ', $joinExprProductDefaultVendorId);
            $collection->getSelect()->joinLeft(
                array(
                    'product_vendor_id' => $attr->getBackend()->getTable()),
                $joinExprProductVendorId,
                array()
            )
            ->joinLeft(
                array(
                    'product_default_vendor_id' => $attr->getBackend()->getTable()),
                $joinExprProductDefaultVendorId,
                array()
            );
    	
        $this->setCollection($collection);

        return $this;
    }
}
