<?php
class SM_Vendors_Block_Adminhtml_Sales_Order_Creditmemo_Create_Adjustments extends Mage_Adminhtml_Block_Sales_Order_Creditmemo_Create_Adjustments
{
    /**
     * Get credit memo shipping amount depend on configuration settings
     * @return float
     */
    public function getShippingAmount()
    {
        $vendorOrder = Mage::registry('vendor_order');
        
        $shipping = $vendorOrder->getBaseShippingAmount()-$vendorOrder->getBaseShippingRefunded();
        
        return Mage::app()->getStore()->roundPrice($shipping) * 1;
    }
}
