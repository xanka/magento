<?php
class SM_Vendors_Block_Adminhtml_Sales_Order_Invoice_Create_Items extends Mage_Adminhtml_Block_Sales_Order_Invoice_Create_Items
{
    public function getUpdateUrl()
    {
        $params = array('order_id' => $this->getInvoice()->getOrderId());
        Mage::helper('smvendors')->addDoAsVendorToParams($params);
        return $this->getUrl('*/*/updateQty', $params);
    }
}
