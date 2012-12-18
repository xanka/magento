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
 * Adminhtml dashboard recent orders grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class SM_Vendors_Block_Override_Adminhtml_Dashboard_Orders_Grid extends Mage_Adminhtml_Block_Dashboard_Orders_Grid
{

    protected function _prepareCollection()
    {
        
		if($vendor = Mage::helper('smvendors')->getVendorLogin()){
			if (!Mage::helper('core')->isModuleEnabled('Mage_Reports')) {
	            return $this;
	        }
	        $collection = Mage::getResourceModel('smvendors/reports_order_collection')
	            ->addItemCountExpr()
	            ->joinCustomerName('customer')
	            ->orderByCreatedAt()
				->setPageSize(5);
	        if($this->getParam('store') || $this->getParam('website') || $this->getParam('group')) {
	            if ($this->getParam('store')) {
	                $collection->addAttributeToFilter('store_id', $this->getParam('store'));
	            } else if ($this->getParam('website')){
	                $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
	                $collection->addAttributeToFilter('store_id', array('in' => $storeIds));
	            } else if ($this->getParam('group')){
	                $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
	                $collection->addAttributeToFilter('store_id', array('in' => $storeIds));
	            }
	
	            $collection->addRevenueToSelect();
	        } else {
	            $collection->addRevenueToSelect(true);
	        }
			
			//die($collection->getSelect());
	        $this->setCollection($collection);
        	return $this;
		}
		return parent::_prepareCollection();	
    }

    /**
     * Prepares page sizes for dashboard grid with las 5 orders
     *
     * @return void
     */
    protected function _preparePage()
    {
        $this->getCollection()->setPageSize($this->getParam($this->getVarNameLimit(), $this->_defaultLimit));
        // Remove count of total orders $this->getCollection()->setCurPage($this->getParam($this->getVarNamePage(), $this->_defaultPage));
    }


    public function getRowUrl($row)
    {
        return $this->getUrl('*/vendors_order/view', array('order_id'=>$row->getOrderId()));
    }
}
