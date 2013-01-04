<?php
class SM_Vendors_Block_Adminhtml_Sales_Order_Shipment_Create extends Mage_Adminhtml_Block_Sales_Order_Shipment_Create
{
    public function getHeaderText()
    {
        $vendorOrder = Mage::registry('vendor_order');
        
        $header = Mage::helper('sales')->__('New Shipment for Order #%s', $vendorOrder->getIncrementId());
        return $header;
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/vendors_order/view', array('order_id'=>$this->getShipment()->getOrderId()));
    }
}
