<?php
class SM_Vendors_Block_Adminhtml_Sales_Order_Shipment_Create_Form extends Mage_Adminhtml_Block_Sales_Order_Shipment_Create_Form
{
    public function getSaveUrl()
    {
        $params = array('order_id' => $this->getShipment()->getOrderId());
        Mage::helper('smvendors')->addDoAsVendorToParams($params);
        return $this->getUrl('*/*/save', $params);
    }
}
