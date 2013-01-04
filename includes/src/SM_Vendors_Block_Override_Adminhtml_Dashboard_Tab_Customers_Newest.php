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
 * Adminhtml dashboard most recent customers grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class SM_Vendors_Block_Override_Adminhtml_Dashboard_Tab_Customers_Newest extends Mage_Adminhtml_Block_Dashboard_Tab_Customers_Newest
{

    protected function _prepareCollection()
    {
    	if($vendor = Mage::helper('smvendors')->getVendorLogin()){
	        $collection = Mage::getResourceModel('smvendors/reports_customer_collection')
	        	->joinVendorCustomer()
	            ->addCustomerName();
	
	        $storeFilter = 0;
	        if ($this->getParam('store')) {
	            $collection->addAttributeToFilter('store_id', $this->getParam('store'));
	            $storeFilter = 1;
	        } else if ($this->getParam('website')){
	            $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
	            $collection->addAttributeToFilter('store_id', array('in' => $storeIds));
	        } else if ($this->getParam('group')){
	            $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
	            $collection->addAttributeToFilter('store_id', array('in' => $storeIds));
	        }
	
	        $collection->addOrdersStatistics($storeFilter)
	            ->orderByCustomerRegistration();
	        $this->setCollection($collection);
			$grandParent = $this->getGrandParent();
	        return call_user_func(array($grandParent, '_prepareCollection'));
    	}
    	return parent::_prepareCollection();
    }
	public function getGrandParent(){
		$grandParent = get_parent_class(get_parent_class($this));
		return $grandParent;
	}
}
