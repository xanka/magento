<?php
/**
 * Report order collection
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class SM_Vendors_Model_Mysql4_Reports_VendorOrders_UpdatedAtCollection extends SM_Vendors_Model_Mysql4_Reports_VendorOrders_CollectionAbstract 
{
    public function __construct(){
		parent::__construct();
		$this->_type = 'updated_at';
	}
}
