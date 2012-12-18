<?php
class SM_Vendors_Block_Adminhtml_Sales_Order_Invoice_Create extends Mage_Adminhtml_Block_Sales_Order_Invoice_Create
{
    /**
     * Retrieve text for header
     *
     * @return string
     */
    public function getHeaderText()
    {
        $vendorOrder = Mage::registry('vendor_order');
        
        return ($this->getInvoice()->getOrder()->getForcedDoShipmentWithInvoice())
            ? Mage::helper('sales')->__('New Invoice and Shipment for Order #%s', $vendorOrder->getIncrementId())
            : Mage::helper('sales')->__('New Invoice for Order #%s', $vendorOrder->getIncrementId());
    }

    /**
     * Retrieve back url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/vendors_order/view', array('order_id'=>$this->getInvoice()->getOrderId()));
    }
}
