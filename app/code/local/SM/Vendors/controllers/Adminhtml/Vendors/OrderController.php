<?php
require_once Mage::getBaseDir('app') . '/code/core/Mage/Adminhtml/controllers/Sales/OrderController.php';

class SM_Vendors_Adminhtml_Vendors_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    protected function _initOrder()
    {
        $order = parent::_initOrder();
        if($order && ($vendor = Mage::helper('smvendors')->getVendorLogin())) {
            $vendorOrder = Mage::getModel('smvendors/order')->getByOriginOrderId($order->getId(), $vendor->getId());
            if(!$vendorOrder->getId()) {
                $this->_getSession()->addError($this->__('This order no longer exists.'));
                $this->_redirect('*/*/');
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                return false;                
            }
            
            Mage::register('vendor_order', $vendorOrder);
        }
        
        return $order;
    }

    protected function _initAction()
    {
        $this->loadLayout()
        ->_setActiveMenu('smvendors/vendors_orders')
        ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
        ->_addBreadcrumb($this->__('Orders'), $this->__('Orders'));
        return $this;
    }
        
    public function viewAction()
    {
        if(!Mage::helper('smvendors')->getVendorLogin()) {
            $this->_redirect('*/sales_order/view', array('order_id' => $this->getRequest()->getParam('order_id')));
            return;
        }
        
        $this->_title($this->__('Sales'))->_title($this->__('Orders'));
        
        if ($order = $this->_initOrder()) {
            $this->_initAction();
        
            $this->_title(sprintf("#%s", Mage::registry('vendor_order')->getIncrementId()));
        
            $this->renderLayout();
        }        
    }
    
    /**
     * Add order comment action
     */
    public function addCommentAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $vendorOrder = Mage::registry('vendor_order');
                $response = false;
                $data = $this->getRequest()->getPost('history');
                $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
                $visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;
    
                // HiepHM add vendor id to history entry
                $status = $order->getStatus();
                $history = $order->addStatusHistoryComment($data['comment'], $data['status'])
                ->setIsVisibleOnFront($visible)
                ->setIsCustomerNotified($notify);
                $history->setVendorId($vendorOrder->getVendorId());
                $order->setStatus($status);
                
                $comment = trim(strip_tags($data['comment']));
    
                $order->save();
                Mage::helper('smvendors/email')->sendUpdateOrderEmail($vendorOrder, $order, $notify, $comment);
    
                $this->loadLayout('empty');
                $this->renderLayout();
            }
            catch (Mage_Core_Exception $e) {
                $response = array(
                        'error'     => true,
                        'message'   => $e->getMessage(),
                );
            }
            catch (Exception $e) {
                $response = array(
                        'error'     => true,
                        'message'   => $this->__('Cannot add order history.')
                );
            }
            if (is_array($response)) {
                $response = Mage::helper('core')->jsonEncode($response);
                $this->getResponse()->setBody($response);
            }
        }
    }

    /**
     * Generate order history for ajax request
     */
    public function commentsHistoryAction()
    {
        $this->_initOrder();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('smvendors/adminhtml_sales_order_view_tab_history')->toHtml()
        );
    }    
    
    /**
     * Notify user
     */
    public function emailAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $vendorOrder = Mage::registry('vendor_order');
                Mage::helper('smvendors/email')->sendNewOrderEmail($vendorOrder, $order);
                $historyItem = Mage::getResourceModel('sales/order_status_history_collection')
                    ->getUnnotifiedForInstance($order, Mage_Sales_Model_Order::HISTORY_ENTITY_NAME);
                if ($historyItem) {
                    $historyItem->setIsCustomerNotified(1);
                    $historyItem->save();
                }
                $this->_getSession()->addSuccess($this->__('The order email has been sent.'));
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Failed to send the order email.'));
                Mage::logException($e);
            }
        }
        $this->_redirect('*/vendors_order/view', array('order_id' => $order->getId()));
    }
    /**
     * Cancel order
     */
    public function cancelAction()
    {
        $order = $this->_initOrder();
        $vendorOrder = Mage::registry('vendor_order');
        if ($order && $vendorOrder) {
            try {
                $vendorOrder->registerCancel($order);
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($vendorOrder)
                    ->addObject($order)
                    ->addObject($vendorOrder->getAllVendorItems())
                    ->save()
                    ;
                $this->_getSession()->addSuccess(
                    $this->__('The order has been cancelled.')
                );
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order has not been cancelled.'));
                Mage::logException($e);
            }
            $this->_redirect('*/vendors_order/view', array('order_id' => $order->getId()));
        }
    }
    
    protected function _isAllowed()
    {
        return true;
        $action = strtolower($this->getRequest()->getActionName());
        switch ($action) {
            case 'hold':
                $aclResource = 'smvendors/vendors_orders/actions/hold';
                break;
            case 'unhold':
                $aclResource = 'smvendors/vendors_orders/actions/unhold';
                break;
            case 'email':
                $aclResource = 'smvendors/vendors_orders/actions/email';
                break;
            case 'cancel':
                $aclResource = 'smvendors/vendors_orders/actions/cancel';
                break;
            case 'view':
                $aclResource = 'smvendors/vendors_orders/actions/view';
                break;
            case 'addcomment':
                $aclResource = 'smvendors/vendors_orders/actions/comment';
                break;
            case 'creditmemos':
                $aclResource = 'smvendors/vendors_orders/actions/creditmemo';
                break;
            case 'reviewpayment':
                $aclResource = 'smvendors/vendors_orders/actions/review_payment';
                break;
            default:
                $aclResource = 'smvendors/vendors_orders';
            break;
    
        }
        return Mage::getSingleton('admin/session')->isAllowed($aclResource);
    }

    public function pdfinvoicesAction(){
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                ->setOrderFilter($orderId)
                ->addFieldToFilter('vendor_id', Mage::helper('smvendors')->getVendorLogin()->getId())
                ->load();
                if ($invoices->getSize() > 0) {
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                        'invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
                        'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/*/');
            }
        }
        $this->_redirect('*/*/');
    }
    
    /**
     * Print shipments for selected orders
     */
    public function pdfshipmentsAction(){
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                ->setOrderFilter($orderId)
                ->addFieldToFilter('vendor_id', Mage::helper('smvendors')->getVendorLogin()->getId())
                ->load();
                if ($shipments->getSize()) {
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                        'packingslip'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
                        'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/*/');
            }
        }
        $this->_redirect('*/*/');
    }
    
    /**
     * Print creditmemos for selected orders
     */
    public function pdfcreditmemosAction(){
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
                ->setOrderFilter($orderId)
                ->addFieldToFilter('vendor_id', Mage::helper('smvendors')->getVendorLogin()->getId())
                ->load();
                if ($creditmemos->getSize()) {
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('sales/order_pdf_creditmemo')->getPdf($creditmemos);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_creditmemo')->getPdf($creditmemos);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                        'creditmemo'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
                        'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/*/');
            }
        }
        $this->_redirect('*/*/');
    }
    
    /**
     * Print all documents for selected orders
     */
    public function pdfdocsAction(){
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                ->setOrderFilter($orderId)
                ->addFieldToFilter('vendor_id', Mage::helper('smvendors')->getVendorLogin()->getId())
                ->load();
                if ($invoices->getSize()){
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
    
                $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                ->setOrderFilter($orderId)
                ->addFieldToFilter('vendor_id', Mage::helper('smvendors')->getVendorLogin()->getId())
                ->load();
                if ($shipments->getSize()){
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
    
                $creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
                ->setOrderFilter($orderId)
                ->addFieldToFilter('vendor_id', Mage::helper('smvendors')->getVendorLogin()->getId())
                ->load();
                if ($creditmemos->getSize()) {
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('sales/order_pdf_creditmemo')->getPdf($creditmemos);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_creditmemo')->getPdf($creditmemos);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                        'docs'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf',
                        $pdf->render(), 'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/*/');
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'orders.csv';
        $grid       = $this->getLayout()->createBlock('smvendors/adminhtml_sales_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
    
    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName   = 'orders.xml';
        $grid       = $this->getLayout()->createBlock('smvendors/adminhtml_sales_order_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }    
}
