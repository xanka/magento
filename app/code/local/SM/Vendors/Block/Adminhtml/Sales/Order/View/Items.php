<?php
class SM_Vendors_Block_Adminhtml_Sales_Order_View_Items extends Mage_Adminhtml_Block_Sales_Order_View_Items
{
    /**
     * Retrieve order items collection
     *
     * @return unknown
     */
    public function getItemsCollection()
    {
        if($vendorOrder = Mage::registry('vendor_order')) {
            $collection = array();
            foreach ($this->getOrder()->getItemsCollection() as $item) {
                if($item->getData('vendor_id') == $vendorOrder->getData('vendor_id')) {
                    $collection[] = $item;
                }
            }
        } else {
            $collection = parent::getItemsCollection();
        }
        
        return $collection;
    }
    
    protected function _getButtonsHtml(SM_Vendors_Model_Order $vendorOrder, Mage_Sales_Model_Order $order, $vendorId)
    {
        $buttonGroups = array();
        $urlParams = array('order_id'=> $order->getId(),'do_as_vendor' => $vendorId);
        if ($vendorOrder->canCancel()) {
            $message = Mage::helper('sales')->__('Are you sure you want to cancel this order?');
            $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                    'id'      => 'order_cancel_' . $vendorId,
                    'label'   => Mage::helper('sales')->__('Cancel'),
                    'onclick'   => 'deleteConfirm(\''.$message.'\', \'' . $this->getUrl('*/vendors_order/cancel', $urlParams) . '\')',
            ));
            $buttonGroups[] = $button->toHtml();
        }
        
        if ($vendorOrder->canInvoice()) {
            $_label = $order->getForcedDoShipmentWithInvoice() ?
            Mage::helper('sales')->__('Invoice and Ship') :
            Mage::helper('sales')->__('Invoice');
            $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                    'id'      => 'order_invoice_' . $vendorId,
                    'label'     => $_label,
                    'onclick'   => 'setLocation(\'' . $this->getUrl('*/vendors_order_invoice/start', $urlParams) . '\')',
                    'class'     => 'go'
            ));
            $buttonGroups[] = $button->toHtml();
        }
        

        if ($vendorOrder->canShip()
                && !$order->getForcedDoShipmentWithInvoice()) {
            $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                    'id'        => 'order_ship_' . $vendorId,
                    'label'     => Mage::helper('sales')->__('Ship'),
                    'onclick'   => 'setLocation(\'' . $this->getUrl('*/vendors_order_shipment/new', $urlParams) . '\')',
                    'class'     => 'go'
            ));
            $buttonGroups[] = $button->toHtml();
        }
        
        if ($vendorOrder->canCreditmemo()) {
            $message = Mage::helper('sales')->__('This will create an offline refund. To create an online refund, open an invoice and create credit memo for it. Do you wish to proceed?');
            $urlParams['_current'] = true;
            $creditMemoUrl = $this->getUrl('*/vendors_order_creditmemo/new', $urlParams);
            $onClick = "setLocation('{$creditMemoUrl}')";
            if ($order->getPayment()->getMethodInstance()->isGateway()) {
                $onClick = "confirmSetLocation('{$message}', '{$creditMemoUrl}')";
            }
            $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                    'id'        => 'order_creditmemo_' . $vendorId,
                    'label'     => Mage::helper('sales')->__('Credit Memo'),
                    'onclick'   => $onClick,
                    'class'     => 'go'
            ));
            $buttonGroups[] = $button->toHtml();
        }
        
        if(!empty($buttonGroups)) {
            return '<p class="form-buttons">' . implode("\n", $buttonGroups) . '</p>'; 
        } else {
            return '';
        }
    }
}
