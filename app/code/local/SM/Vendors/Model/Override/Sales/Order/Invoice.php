<?php
class SM_Vendors_Model_Override_Sales_Order_Invoice extends Mage_Sales_Model_Order_Invoice
{
    public function sendEmail($notifyCustomer = true, $comment = '')
    {
        Mage::helper('smvendors/email')->sendNewInvoiceEmail(null, $this, $notifyCustomer, $comment);
        
        return $this;
    }
    
    public function sendUpdateEmail($notifyCustomer = true, $comment = '')
    {
        Mage::helper('smvendors/email')->sendUpdateInvoiceEmail(null, $this, $notifyCustomer, $comment);
        
        return $this;
    }
}