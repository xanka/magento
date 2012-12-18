<?php
class SM_Vendors_Model_Override_Sales_Service_Order extends Mage_Sales_Model_Service_Order
{
    /**
     * Check if order item can be invoiced. Dummy item can be invoiced or with his childrens or
     * with parent item which is included to invoice
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @param array $qtys
     * @return bool
     */
    protected function _canInvoiceItem($item, $qtys=array())
    {
        $result = parent::_canInvoiceItem($item, $qtys);
        if($vendorOrder = Mage::registry('vendor_order')) {
            return $result && ($vendorOrder->getVendorId() == $item->getVendorId());
        } else {
            return $result;
        }
    }

    /**
     * Check if order item can be shiped. Dummy item can be shiped or with his childrens or
     * with parent item which is included to shipment
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @param array $qtys
     * @return bool
     */
    protected function _canShipItem($item, $qtys=array())
    {
        $result = parent::_canShipItem($item, $qtys);
        if($vendorOrder = Mage::registry('vendor_order')) {
            return $result && ($vendorOrder->getVendorId() == $item->getVendorId());
        } else {
            return $result;
        }
    }

    /**
     * Check if order item can be refunded
     *
     * @param Mage_Sales_Model_Order_Item $item
     * @param array $qtys
     * @param array $invoiceQtysRefundLimits
     * @return bool
     */
    protected function _canRefundItem($item, $qtys=array(), $invoiceQtysRefundLimits=array())
    {
        $result = parent::_canRefundItem($item, $qtys, $invoiceQtysRefundLimits);
        if($vendorOrder = Mage::registry('vendor_order')) {
            return $result && ($vendorOrder->getVendorId() == $item->getVendorId());
        } else {
            return $result;
        }        
    }
    
    public function prepareInvoice($qtys = array())
    {
        $invoice = parent::prepareInvoice($qtys);
        if($vendorOrder = Mage::registry('vendor_order')) {
            $order = $invoice->getOrder();
            
            $dummyOrder = Mage::getModel('sales/order');
            $dummyOrder->setData($vendorOrder->getData());
            $dummyOrder->setId($order->getId());
            
            $previousInvoices = $dummyOrder->getInvoiceCollection();
            foreach ($previousInvoices as $key => $oldInvoice) {
                if($oldInvoice->getVendorId() != $vendorOrder->getVendorId()) {
                    $previousInvoices->removeItemByKey($key);
                }
            }
            
            $invoice->setOrder($dummyOrder);
            $invoice->setGrandTotal(0);
            $invoice->setBaseGrandTotal(0);
            $invoice->collectTotals();
            $invoice->setOrder($order);
        }
        
        return $invoice;
    }
    
    public function prepareCreditmemo($data = array())
    {
        $creditmemo = parent::prepareCreditmemo($data);
        if($vendorOrder = Mage::registry('vendor_order')) {
            $order = $creditmemo->getOrder();
            
            $dummyOrder = Mage::getModel('sales/order');
            $dummyOrder->setData($vendorOrder->getData());
            $dummyOrder->setBaseCurrencyCode($order->getBaseCurrencyCode());
            $dummyOrder->setStoreId($order->getStoreId());
            
            $shippingRefundAllow = $dummyOrder->getBaseShippingAmount() - $dummyOrder->getBaseShippingRefunded();
            if($creditmemo->hasBaseShippingAmount() &&
                    $creditmemo->getBaseShippingAmount() > $shippingRefundAllow) {
                $creditmemo->setBaseShippingAmount($shippingRefundAllow);
            }
            
            
            $creditmemo->setOrder($dummyOrder);
//             $creditmemo->setShippingAmount(0);
//             $creditmemo->setBaseShippingAmount(0);
//             $creditmemo->setShippingInclTax(0);
//             $creditmemo->setBaseShippingInclTax(0);            
            $creditmemo->setGrandTotal(0);
            $creditmemo->setBaseGrandTotal(0);
            $creditmemo->collectTotals();
            $creditmemo->setOrder($order);            
        }
        
        return $creditmemo;
    }    
}
