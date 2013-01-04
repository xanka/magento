<?php
class SM_Vendors_Model_Override_Sales_Order_Shipment extends Mage_Sales_Model_Order_Shipment
{
    public function sendEmail($notifyCustomer = true, $comment = '')
    {
        Mage::helper('smvendors/email')->sendNewShipmentEmail(null, $this, $notifyCustomer, $comment);
        
        return $this;
    }
    
    public function sendUpdateEmail($notifyCustomer = true, $comment = '')
    {
        Mage::helper('smvendors/email')->sendUpdateShipmentEmail(null, $this, $notifyCustomer, $comment);
        
        return $this;
    }
}