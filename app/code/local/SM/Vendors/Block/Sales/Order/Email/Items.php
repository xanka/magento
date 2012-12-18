<?php
class SM_Vendors_Block_Sales_Order_Email_Items extends Mage_Sales_Block_Items_Abstract
{
    protected $_vendorOrders = array();
    
    public function getVendorOrders()
    {
        if(empty($this->_vendorOrders)) {
            if($this->getVendorOrder()) {
                //Send mail from vendor logged in
                $this->_vendorOrders = array($this->getVendorOrder());
            } else {
                //Send mail from admin logged in
                $order = $this->getOrder();
                if($order->getId()) {
                    $collection = Mage::getResourceModel('smvendors/order_collection')
                                            ->addFieldToFilter('order_id', $order->getId());
                    $this->_vendorOrders = $collection->getItems();
                }
            }
        }
        
        return $this->_vendorOrders;
    }
}
