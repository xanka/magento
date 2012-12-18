<?php
require_once Mage::getBaseDir('app') . '/code/core/Mage/Adminhtml/controllers/Sales/Order/InvoiceController.php';
class SM_Vendors_Adminhtml_Vendors_Order_InvoiceController extends Mage_Adminhtml_Sales_Order_InvoiceController
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('smvendors/vendors_invoices');
    }
    
    protected function _initAction()
    {
        $this->loadLayout()
        ->_setActiveMenu('smvendors/vendors_invoices')
        ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
        ->_addBreadcrumb($this->__('Invoices'),$this->__('Invoices'));
        return $this;
    }
    
    protected function _initInvoice($update = false)
    {
        $this->_title($this->__('Sales'))->_title($this->__('Invoices'));
        
        $invoice = false;
        $itemsToInvoice = 0;
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if ($invoiceId) {
            $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
            if (!$invoice->getId()) {
                $this->_getSession()->addError($this->__('The invoice no longer exists.'));
                return false;
            }
            
            /**
             * Load vendor order
             */
            if($vendor = Mage::helper('smvendors')->getVendorLogin()) {
                $vendorOrder = Mage::getModel('smvendors/order')->getByOriginOrderId($invoice->getOrder()->getId(), $vendor->getId());
                if(!$vendorOrder->getId()) {
                    $this->_getSession()->addError($this->__('This vendor order no longer exists.'));
                    $this->_redirect('*/*/');
                    $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                    return false;
                }
            
                Mage::register('vendor_order', $vendorOrder);
            }
            
        } elseif ($orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            
            /**
             * Load vendor order
             */
            if($vendor = Mage::helper('smvendors')->getVendorLogin()) {
                $vendorOrder = Mage::getModel('smvendors/order')->getByOriginOrderId($orderId, $vendor->getId());
                if(!$vendorOrder->getId()) {
                    $this->_getSession()->addError($this->__('This vendor order no longer exists.'));
                    $this->_redirect('*/*/');
                    $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                    return false;
                }
            
                Mage::register('vendor_order', $vendorOrder);
            }
            
            /**
             * Check order existing
             */
            if (!$order->getId()) {
                $this->_getSession()->addError($this->__('The order no longer exists.'));
                return false;
            }
            /**
             * Check invoice create availability
             */
            if (!$order->canInvoice()) {
                $this->_getSession()->addError($this->__('The order does not allow creating an invoice.'));
                return false;
            }
            $savedQtys = $this->_getItemQtys();
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($savedQtys);
            if (!$invoice->getTotalQty()) {
                Mage::throwException($this->__('Cannot create an invoice without products.'));
            }
        }
        
        Mage::register('current_invoice', $invoice);
        return $invoice;
    }
    
    /**
     * Invoices grid
     */
    public function indexAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Invoices'));
    
        $this->_initAction()
        ->_addContent($this->getLayout()->createBlock('smvendors/adminhtml_sales_invoice'))
        ->renderLayout();
    }
    
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('smvendors/adminhtml_sales_invoice_grid')->toHtml()
        );
    }
    
    /**
     * Start create invoice action
     */
    public function startAction()
    {
        /**
         * Clear old values for invoice qty's
         */
        $this->_getSession()->getInvoiceItemQtys(true);
        $params = array('order_id'=>$this->getRequest()->getParam('order_id'));
        Mage::helper('smvendors')->addDoAsVendorToParams($params);
        $this->_redirect('*/*/new', $params);
    }
            
    /**
     * Invoice create page
     */
    public function newAction()
    {
        $invoice = $this->_initInvoice();
        if ($invoice) {
            $this->_title($this->__('New Invoice'));
    
            if ($comment = Mage::getSingleton('adminhtml/session')->getCommentText(true)) {
                $invoice->setCommentText($comment);
            }
    
            $this->loadLayout()
            ->_setActiveMenu('smvendors/vendors_orders')
            ->renderLayout();
        } else {
            $this->_redirect('*/vendors_order/view', array('order_id'=>$this->getRequest()->getParam('order_id')));
        }
    }

    /**
     * Save invoice
     * We can save only new invoice. Existing invoices are not editable
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('invoice');
        $orderId = $this->getRequest()->getParam('order_id');
    
        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }
    
        try {
            $invoice = $this->_initInvoice();
            if ($invoice) {
    
                if (!empty($data['capture_case'])) {
                    $invoice->setRequestedCaptureCase($data['capture_case']);
                }
    
                if (!empty($data['comment_text'])) {
                    $invoice->addComment(
                            $data['comment_text'],
                            isset($data['comment_customer_notify']),
                            isset($data['is_visible_on_front'])
                    );
                }
    
                $invoice->register();
    
                if (!empty($data['send_email'])) {
                    $invoice->setEmailSent(true);
                }
    
                $invoice->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $invoice->getOrder()->setIsInProcess(true);
                
                $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
                $shipment = false;
                if (!empty($data['do_shipment']) || (int) $invoice->getOrder()->getForcedDoShipmentWithInvoice()) {
                    $shipment = $this->_prepareShipment($invoice);
                    if ($shipment) {
                        $shipment->setEmailSent($invoice->getEmailSent());
                        $transactionSave->addObject($shipment);
                    }
                }
                
                // HiepHM Process vendor order
                if($vendorOrder = Mage::registry('vendor_order')) {
                    /* @var $vendorOrder SM_Vendors_Model_Order */
                    $vendorOrder->registerInvoice($invoice);
                    $transactionSave->addObject($vendorOrder);
                }
                
                $transactionSave->save();
    
                if (isset($shippingResponse) && $shippingResponse->hasErrors()) {
                    $this->_getSession()->addError($this->__('The invoice and the shipment  have been created. The shipping label cannot be created at the moment.'));
                } elseif (!empty($data['do_shipment'])) {
                    $this->_getSession()->addSuccess($this->__('The invoice and shipment have been created.'));
                } else {
                    $this->_getSession()->addSuccess($this->__('The invoice has been created.'));
                }
    
                // send invoice/shipment emails
                $comment = '';
                if (isset($data['comment_customer_notify'])) {
                    $comment = $data['comment_text'];
                }
                try {
                    Mage::helper('smvendors/email')->sendNewInvoiceEmail($vendorOrder, $invoice, !empty($data['send_email']), $comment);
                    //$invoice->sendEmail(!empty($data['send_email']), $comment);
                } catch (Exception $e) {
                    Mage::logException($e);
                    $this->_getSession()->addError($this->__('Unable to send the invoice email.'));
                }
                if ($shipment) {
                    try {
                        $shipment->sendEmail(!empty($data['send_email']));
                    } catch (Exception $e) {
                        Mage::logException($e);
                        $this->_getSession()->addError($this->__('Unable to send the shipment email.'));
                    }
                }
                Mage::getSingleton('adminhtml/session')->getCommentText(true);
                $this->_redirect('*/vendors_order/view', array('order_id' => $orderId));
            } else {
                $this->_redirect('*/*/new', array('order_id' => $orderId));
            }
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Unable to save the invoice.'));
            Mage::logException($e);
        }
        $this->_redirect('*/*/new', array('order_id' => $orderId));
    } 

    /**
     * Invoice information page
     */
    public function viewAction()
    {
        $invoice = $this->_initInvoice();
        if ($invoice) {
            $this->_title(sprintf("#%s", $invoice->getIncrementId()));

            $this->loadLayout()
                ->_setActiveMenu('smvendors/vendors_orders');
            $this->getLayout()->getBlock('sales_invoice_view')
                ->updateBackButtonUrl($this->getRequest()->getParam('come_from'));
            $this->renderLayout();
        }
        else {
            $this->_forward('noRoute');
        }
    }

    public function addCommentAction()
    {
        try {
            $this->getRequest()->setParam('invoice_id', $this->getRequest()->getParam('id'));
            $data = $this->getRequest()->getPost('comment');
            if (empty($data['comment'])) {
                Mage::throwException($this->__('The Comment Text field cannot be empty.'));
            }
            $invoice = $this->_initInvoice();
            $invoice->addComment(
                    $data['comment'],
                    isset($data['is_customer_notified']),
                    isset($data['is_visible_on_front'])
            );
            Mage::helper('smvendors/email')->sendUpdateInvoiceEmail(Mage::registry('vendor_order'), 
                    $invoice, $data['is_customer_notified'], $data['comment']);
            //$invoice->sendUpdateEmail(!empty($data['is_customer_notified']), $data['comment']);
            $invoice->save();
    
            $this->loadLayout();
            $response = $this->getLayout()->getBlock('invoice_comments')->toHtml();
        } catch (Mage_Core_Exception $e) {
            $response = array(
                    'error'     => true,
                    'message'   => $e->getMessage()
            );
            $response = Mage::helper('core')->jsonEncode($response);
        } catch (Exception $e) {
            $response = array(
                    'error'     => true,
                    'message'   => $this->__('Cannot add new comment.')
            );
            $response = Mage::helper('core')->jsonEncode($response);
        }
        $this->getResponse()->setBody($response);
    }

    /**
     * Notify user
     */
    public function emailAction()
    {
        if ($invoiceId = $this->getRequest()->getParam('invoice_id')) {
            if ($invoice = Mage::getModel('sales/order_invoice')->load($invoiceId)) {
                //$invoice->sendEmail();
                $vendorOrder = Mage::getModel('smvendors/order')->getByOriginOrderId($invoice->getOrder()->getId(), $invoice->getVendorId());
                Mage::helper('smvendors/email')->sendNewInvoiceEmail($vendorOrder, $invoice);
                $historyItem = Mage::getResourceModel('sales/order_status_history_collection')
                ->getUnnotifiedForInstance($invoice, Mage_Sales_Model_Order_Invoice::HISTORY_ENTITY_NAME);
                if ($historyItem) {
                    $historyItem->setIsCustomerNotified(1);
                    $historyItem->save();
                }
                $this->_getSession()->addSuccess(Mage::helper('sales')->__('The message has been sent.'));
                $this->_redirect('*/vendors_order_invoice/view', array(
                        'order_id'  => $invoice->getOrder()->getId(),
                        'invoice_id'=> $invoiceId,
                ));
            }
        }
    }

    /**
     * Export invoice grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'invoices.csv';
        $grid       = $this->getLayout()->createBlock('smvendors/adminhtml_sales_invoice_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
    
    /**
     *  Export invoice grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName   = 'invoices.xml';
        $grid       = $this->getLayout()->createBlock('smvendors/adminhtml_sales_invoice_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }    
}
