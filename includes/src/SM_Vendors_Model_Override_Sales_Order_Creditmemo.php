<?php
class SM_Vendors_Model_Override_Sales_Order_Creditmemo extends Mage_Sales_Model_Order_Creditmemo
{
    public function sendEmail($notifyCustomer = true, $comment = '')
    {
        Mage::helper('smvendors/email')->sendNewCreditmemoEmail(null, $this, $notifyCustomer, $comment);
        
        return $this;
    }
    
    public function sendUpdateEmail($notifyCustomer = true, $comment = '')
    {
        Mage::helper('smvendors/email')->sendUpdateCreditmemoEmail(null, $this, $notifyCustomer, $comment);
        
        return $this;
    }
}