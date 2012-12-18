<?php
class SM_Vendors_Block_Adminhtml_Sales_Order_View_Tab_History extends Mage_Adminhtml_Block_Sales_Order_View_Tab_History
{
    /**
     * Compose and get order full history.
     * Consists of the status history comments as well as of invoices, shipments and creditmemos creations
     * @return array
     */
    public function getFullHistory()
    {
        $vendorOrder = Mage::registry('vendor_order');
        if(!$vendorOrder) {
            return parent::getFullHistory();
        }
        
        $vendorId = $vendorOrder->getVendorId();
        $order = $this->getOrder();

        $history = array();
        foreach ($order->getAllStatusHistory() as $orderComment){
            if($orderComment->getData('vendor_id') != 0 
                    && $orderComment->getData('vendor_id') != $vendorId) {
                continue;
            }
            // Add id to key to prevent duplicate with item below (invoice, shipment,...)
            $history[$orderComment->getCreatedAt() . $orderComment->getEntityId()] = $this->_prepareHistoryItem(
                $orderComment->getStatusLabel(),
                $orderComment->getIsCustomerNotified(),
                $orderComment->getCreatedAtDate(),
                $orderComment->getComment()
            );
        }

        foreach ($order->getCreditmemosCollection() as $_memo){
            if($_memo->getData('vendor_id') != $vendorId) {
                continue;
            }
            $history[$_memo->getCreatedAt()] =
                $this->_prepareHistoryItem($this->__('Credit memo #%s created', $_memo->getIncrementId()),
                    $_memo->getEmailSent(), $_memo->getCreatedAtDate());

            foreach ($_memo->getCommentsCollection() as $_comment){
                $history[$_comment->getCreatedAt()] =
                    $this->_prepareHistoryItem($this->__('Credit memo #%s comment added', $_memo->getIncrementId()),
                        $_comment->getIsCustomerNotified(), $_comment->getCreatedAtDate(), $_comment->getComment());
            }
        }

        foreach ($order->getShipmentsCollection() as $_shipment){
            if($_shipment->getData('vendor_id') != $vendorId) {
                continue;
            }
            $history[$_shipment->getCreatedAt()] =
                $this->_prepareHistoryItem($this->__('Shipment #%s created', $_shipment->getIncrementId()),
                    $_shipment->getEmailSent(), $_shipment->getCreatedAtDate());

            foreach ($_shipment->getCommentsCollection() as $_comment){
                $history[$_comment->getCreatedAt()] =
                    $this->_prepareHistoryItem($this->__('Shipment #%s comment added', $_shipment->getIncrementId()),
                        $_comment->getIsCustomerNotified(), $_comment->getCreatedAtDate(), $_comment->getComment());
            }
        }

        foreach ($order->getInvoiceCollection() as $_invoice){
            if($_invoice->getData('vendor_id') != $vendorId) {
                continue;
            }
            $history[$_invoice->getCreatedAt()] =
                $this->_prepareHistoryItem($this->__('Invoice #%s created', $_invoice->getIncrementId()),
                    $_invoice->getEmailSent(), $_invoice->getCreatedAtDate());

            foreach ($_invoice->getCommentsCollection() as $_comment){
                $history[$_comment->getCreatedAt()] =
                    $this->_prepareHistoryItem($this->__('Invoice #%s comment added', $_invoice->getIncrementId()),
                        $_comment->getIsCustomerNotified(), $_comment->getCreatedAtDate(), $_comment->getComment());
            }
        }

        foreach ($order->getTracksCollection() as $_track){
            if($_track->getData('vendor_id') != $vendorId) {
                continue;
            }
            $history[$_track->getCreatedAt()] =
                $this->_prepareHistoryItem($this->__('Tracking number %s for %s assigned', $_track->getNumber(), $_track->getTitle()),
                    false, $_track->getCreatedAtDate());
        }

        krsort($history);
        return $history;
    }
}
