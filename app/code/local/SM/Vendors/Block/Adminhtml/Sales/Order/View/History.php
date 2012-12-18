<?php
class SM_Vendors_Block_Adminhtml_Sales_Order_View_History extends Mage_Adminhtml_Block_Sales_Order_View_History
{
    public function canAddComment()
    {
        return Mage::getSingleton('admin/session')->isAllowed('smvendors/vendors_orders/actions/comment') &&
               $this->getOrder()->canComment();
    }
    
    public function getStatusHistoryCollection()
    {
        $statusHistory = Mage::getResourceModel('sales/order_status_history_collection')
        ->setOrderFilter($this->getOrder())
        ->setOrder('created_at', 'desc')
        ->setOrder('entity_id', 'desc');

        if($vendorOrder = Mage::registry('vendor_order')) {
            $statusHistory->addFieldToFilter('vendor_id', array(0, $vendorOrder->getVendorId()));
        }
        
        if ($this->getOrder()->getId()) {
            foreach ($statusHistory as $status) {
                $status->setOrder($this->getOrder());
            }
        }
        return $statusHistory;
    }
    
    public function getStatusLabel()
    {
        if($vendorOrder = Mage::registry('vendor_order')) {
            return Mage::getSingleton('sales/order_config')->getStatusLabel($vendorOrder->getStatus());
        } else {
            return $this->getOrder()->getStatusLabel();
        }
    }
    
    public function getStatuses()
    {
        if($vendorOrder = Mage::registry('vendor_order')) {
            return array($vendorOrder->getStatus() 
                    => Mage::getSingleton('sales/order_config')->getStatusLabel($vendorOrder->getStatus()));
        } else {
            return parent::getStatuses();
        }
    }    
}
