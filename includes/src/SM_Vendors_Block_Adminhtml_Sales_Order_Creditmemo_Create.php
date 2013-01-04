<?php
class SM_Vendors_Block_Adminhtml_Sales_Order_Creditmemo_Create extends Mage_Adminhtml_Block_Sales_Order_Creditmemo_Create
{
    public function getHeaderText()
    {
        $vendorOrder = Mage::registry('vendor_order');
        
        if ($this->getCreditmemo()->getInvoice()) {
            $header = Mage::helper('sales')->__('New Credit Memo for Invoice #%s',
                $this->getCreditmemo()->getInvoice()->getIncrementId()
            );
        }
        else {
            $header = Mage::helper('sales')->__('New Credit Memo for Order #%s',
                $vendorOrder->getIncrementId()
            );
        }
        return $header;
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/vendors_order/view', array('order_id'=>$this->getCreditmemo()->getOrderId()));
    }
}
