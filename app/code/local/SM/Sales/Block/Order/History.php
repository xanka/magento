<?php
/**
 * @category    SM
 * @package     SM_Sales
 * @copyright   Copyright (c) 2012 SmartOSC (www.smartosc.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SM_Sales_Block_Order_History extends Mage_Sales_Block_Order_History
{
	/**
	 * 
	 * @var SM_Vendors_Model_Mysql4_Order_Collection
	 */
	protected $_vendorOrders = null;
	
	/**
	 * 
	 */
	public function getVendorOrders()
	{
		if (is_null($this->_vendorOrders)) {
			$collection = Mage::getResourceModel('smvendors/order_collection');
			$collection->getSelect()
				->join(array('main_order' => Mage::getSingleton('core/resource')->getTableName('sales/order')),
						'main_table.order_id = main_order.entity_id',
						array('main_order.created_at'))
				->where('main_order.customer_id = ?', Mage::getSingleton('customer/session')->getCustomer()->getId())
				->where('main_order.state IN (?)', Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates())
				->order('main_order.created_at DESC')
				->order('main_table.vendor_id ASC');
			
			$this->_vendorOrders = $collection;
		}

		return $this->_vendorOrders;
	}
	
	/**
	 *
	 * @return string
	 */
    public function getPrintUrl()
    {
        return $this->getUrl('*/*/printOrders');
    }
    
    /**
     * (non-PHPdoc)
     * @see Mage_Sales_Block_Order_History::_prepareLayout()
     */
    protected function _prepareLayout()
    {
    	$pager = $this->getLayout()->createBlock('page/html_pager', 'sales.order.history.pager')
	    	->setCollection($this->getVendorOrders());
    	$this->setChild('pager', $pager);
    	$this->getVendorOrders()->load();
    	return $this;
    }
}
