<?php
class SM_Vendors_Block_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View
{
    public function __construct()
    {
        parent::__construct();
        
        $vendorOrder = Mage::registry('vendor_order');
        if($vendorOrder) {
            if(!$vendorOrder->canCancel()) {
                $this->_removeButton('order_cancel');
            }
            
            if(!$vendorOrder->canCreditmemo()) {
                $this->_removeButton('order_creditmemo');
            }
            
            if(!$vendorOrder->canInvoice()) {
                $this->_removeButton('order_invoice');
            }
            
            if(!$vendorOrder->canShip()) {
                $this->_removeButton('order_ship');
            }
            
            if(true) {
                $this->_removeButton('order_edit');
            }
            
            if(true) {
                $this->_removeButton('send_notification');
            }
            
            if(!$vendorOrder->canReorder()) {
                $this->_removeButton('order_reorder');
            }
            
            if($vendorOrder->isCanceled()) {
                $this->_removeButton('send_notification');
            }
        }
    }
    
    public function getHeaderText()
    {
        return Mage::helper('sales')->__('Order # %s | %s', Mage::registry('vendor_order')->getIncrementId(), $this->formatDate($this->getOrder()->getCreatedAtDate(), 'medium', true));
    }
    
    public function getInvoiceUrl()
    {
        return $this->getUrl('*/vendors_order_invoice/start');
    }
    
    public function getCreditmemoUrl()
    {
        return $this->getUrl('*/vendors_order_creditmemo/start');
    }

    public function getShipUrl()
    {
        return $this->getUrl('*/vendors_order_shipment/start');
    }
    
    public function getReorderUrl()
    {
        return $this->getUrl('*/vendors_order_create/reorder');
    }    

    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('smvendors/vendors_orders/actions/' . $action);
    }    
}
