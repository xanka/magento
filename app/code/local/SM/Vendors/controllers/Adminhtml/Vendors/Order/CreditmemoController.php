<?php
require_once Mage::getBaseDir('app') . '/code/core/Mage/Adminhtml/controllers/Sales/Order/CreditmemoController.php';
class SM_Vendors_Adminhtml_Vendors_Order_CreditmemoController extends Mage_Adminhtml_Sales_Order_CreditmemoController
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('smvendors/vendors_creditmemos');
    }
    
    protected function _initAction()
    {
        $this->loadLayout()
        ->_setActiveMenu('smvendors/vendors_creditmemos')
        ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
        ->_addBreadcrumb($this->__('Credit Memos'),$this->__('Credit Memos'));
        return $this;
    }
    
    protected function _initCreditmemo($update = false)
    {
        $this->_title($this->__('Sales'))->_title($this->__('Credit Memos'));
    
        $creditmemo = false;
        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if ($creditmemoId) {
            $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
            
            /**
             * Load vendor order
             */
            if($vendor = Mage::helper('smvendors')->getVendorLogin()) {
                $vendorOrder = Mage::getModel('smvendors/order')->getByOriginOrderId($creditmemo->getOrder()->getId(), $vendor->getId());
                if(!$vendorOrder->getId()) {
                    $this->_getSession()->addError($this->__('This vendor order no longer exists.'));
                    $this->_redirect('*/*/');
                    $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                    return false;
                }
            
                Mage::register('vendor_order', $vendorOrder);
            }
            
        } elseif ($orderId) {
            $data   = $this->getRequest()->getParam('creditmemo');
            $order  = Mage::getModel('sales/order')->load($orderId);
            $invoice = $this->_initInvoice($order);
    
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
            
            if (!$this->_canCreditmemo($order)) {
                return false;
            }
    
            $savedData = $this->_getItemData();
    
            $qtys = array();
            $backToStock = array();
            foreach ($savedData as $orderItemId =>$itemData) {
                if (isset($itemData['qty'])) {
                    $qtys[$orderItemId] = $itemData['qty'];
                }
                if (isset($itemData['back_to_stock'])) {
                    $backToStock[$orderItemId] = true;
                }
            }
            $data['qtys'] = $qtys;
    
            $service = Mage::getModel('sales/service_order', $order);
            if ($invoice) {
                $creditmemo = $service->prepareInvoiceCreditmemo($invoice, $data);
            } else {
                $creditmemo = $service->prepareCreditmemo($data);
            }
    
            /**
             * Process back to stock flags
             */
            foreach ($creditmemo->getAllItems() as $creditmemoItem) {
                $orderItem = $creditmemoItem->getOrderItem();
                $parentId = $orderItem->getParentItemId();
                if (isset($backToStock[$orderItem->getId()])) {
                    $creditmemoItem->setBackToStock(true);
                } elseif ($orderItem->getParentItem() && isset($backToStock[$parentId]) && $backToStock[$parentId]) {
                    $creditmemoItem->setBackToStock(true);
                } elseif (empty($savedData)) {
                    $creditmemoItem->setBackToStock(Mage::helper('cataloginventory')->isAutoReturnEnabled());
                } else {
                    $creditmemoItem->setBackToStock(false);
                }
            }
        }
    
        $args = array('creditmemo' => $creditmemo, 'request' => $this->getRequest());
        Mage::dispatchEvent('adminhtml_sales_order_creditmemo_register_before', $args);
    
        Mage::register('current_creditmemo', $creditmemo);
        return $creditmemo;
    }
    
    /**
     * Save creditmemo and related order, invoice in one transaction
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     */
    protected function _saveCreditmemo($creditmemo)
    {
        $transactionSave = Mage::getModel('core/resource_transaction')
        ->addObject($creditmemo)
        ->addObject($creditmemo->getOrder());
        if ($creditmemo->getInvoice()) {
            $transactionSave->addObject($creditmemo->getInvoice());
        }
        
        // HiepHM Process vendor order
        if($vendorOrder = Mage::registry('vendor_order')) {
            /* @var $vendorOrder SM_Vendors_Model_Order */
            $vendorOrder->registerCreditmemo($creditmemo);
            $transactionSave->addObject($vendorOrder);
        }
                
        $transactionSave->save();
    
        return $this;
    }
    
    public function indexAction()
    {
        $this->_initAction()
        ->_addContent($this->getLayout()->createBlock('smvendors/adminhtml_sales_creditmemo'))
        ->renderLayout();
    }
        
    /**
     * creditmemo information page
     */
    public function viewAction()
    {
        $creditmemo = $this->_initCreditmemo();
        if ($creditmemo) {
            if ($creditmemo->getInvoice()) {
                $this->_title($this->__("View Memo for #%s", $creditmemo->getInvoice()->getIncrementId()));
            } else {
                $this->_title($this->__("View Memo"));
            }
    
            $this->loadLayout();
            $this->getLayout()->getBlock('sales_creditmemo_view')
            ->updateBackButtonUrl($this->getRequest()->getParam('come_from'));
            $this->_setActiveMenu('sales/order')
            ->renderLayout();
        } else {
            $this->_forward('noRoute');
        }
    }

    public function newAction()
    {
        if ($creditmemo = $this->_initCreditmemo()) {
            if ($creditmemo->getInvoice()) {
                $this->_title($this->__("New Memo for #%s", $creditmemo->getInvoice()->getIncrementId()));
            } else {
                $this->_title($this->__("New Memo"));
            }
    
            if ($comment = Mage::getSingleton('adminhtml/session')->getCommentText(true)) {
                $creditmemo->setCommentText($comment);
            }
    
            $this->loadLayout()
            ->_setActiveMenu('smvendors/vendors_creditmemos')
            ->renderLayout();
        } else {
            $this->_forward('noRoute');
        }
    }

    public function saveAction()
    {
        $data = $this->getRequest()->getPost('creditmemo');
        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }
    
        try {
            $creditmemo = $this->_initCreditmemo();
            if ($creditmemo) {
                if (($creditmemo->getGrandTotal() <=0) && (!$creditmemo->getAllowZeroGrandTotal())) {
                    Mage::throwException(
                            $this->__('Credit memo\'s total must be positive.')
                    );
                }
    
                $comment = '';
                if (!empty($data['comment_text'])) {
                    $creditmemo->addComment(
                            $data['comment_text'],
                            isset($data['comment_customer_notify']),
                            isset($data['is_visible_on_front'])
                    );
                    if (isset($data['comment_customer_notify'])) {
                        $comment = $data['comment_text'];
                    }
                }
    
                if (isset($data['do_refund'])) {
                    $creditmemo->setRefundRequested(true);
                }
                if (isset($data['do_offline'])) {
                    $creditmemo->setOfflineRequested((bool)(int)$data['do_offline']);
                }
    
                $creditmemo->register();
                if (!empty($data['send_email'])) {
                    $creditmemo->setEmailSent(true);
                }
    
                $creditmemo->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $this->_saveCreditmemo($creditmemo);
                //$creditmemo->sendEmail(!empty($data['send_email']), $comment);
                Mage::helper('smvendors/email')->sendNewCreditmemoEmail(Mage::registry('vendor_order'), $creditmemo, !empty($data['send_email']), $comment);
                $this->_getSession()->addSuccess($this->__('The credit memo has been created.'));
                Mage::getSingleton('adminhtml/session')->getCommentText(true);
                $this->_redirect('*/vendors_order/view', array('order_id' => $creditmemo->getOrderId()));
                return;
            } else {
                $this->_forward('noRoute');
                return;
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setFormData($data);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($this->__('Cannot save the credit memo.'));
        }
        $this->_redirect('*/*/new', array('_current' => true));
    }    

    /**
     * Add comment to creditmemo history
     */
    public function addCommentAction()
    {
        try {
            $this->getRequest()->setParam(
                    'creditmemo_id',
                    $this->getRequest()->getParam('id')
            );
            $data = $this->getRequest()->getPost('comment');
            if (empty($data['comment'])) {
                Mage::throwException($this->__('The Comment Text field cannot be empty.'));
            }
            $creditmemo = $this->_initCreditmemo();
            $creditmemo->addComment(
                    $data['comment'],
                    isset($data['is_customer_notified']),
                    isset($data['is_visible_on_front'])
            );
            $creditmemo->save();
            //$creditmemo->sendUpdateEmail(!empty($data['is_customer_notified']), $data['comment']);
            Mage::helper('smvendors/email')->sendUpdateCreditmemoEmail(Mage::registry('vendor_order'), $creditmemo, !empty($data['is_customer_notified']), $data['comment']);
    
            $this->loadLayout();
            $response = $this->getLayout()->getBlock('creditmemo_comments')->toHtml();
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
        if ($creditmemoId = $this->getRequest()->getParam('creditmemo_id')) {
            if ($creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId)) {
                //$creditmemo->sendEmail();
                $vendorOrder = Mage::getModel('smvendors/order')->getByOriginOrderId($creditmemo->getOrder()->getId(), $creditmemo->getVendorId());
                Mage::helper('smvendors/email')->sendNewCreditmemoEmail($vendorOrder, $creditmemo);
                $historyItem = Mage::getResourceModel('sales/order_status_history_collection')
                ->getUnnotifiedForInstance($creditmemo, Mage_Sales_Model_Order_Creditmemo::HISTORY_ENTITY_NAME);
                if ($historyItem) {
                    $historyItem->setIsCustomerNotified(1);
                    $historyItem->save();
                }
    
                $this->_getSession()->addSuccess(Mage::helper('sales')->__('The message was sent.'));
                $this->_redirect('*/*/view', array(
                        'creditmemo_id' => $creditmemoId
                ));
            }
        }
    }
    
    /**
     * Export credit memo grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'creditmemos.csv';
        $grid       = $this->getLayout()->createBlock('smvendors/adminhtml_sales_creditmemo_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
    
    /**
     *  Export credit memo grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName   = 'creditmemos.xml';
        $grid       = $this->getLayout()->createBlock('smvendors/adminhtml_sales_creditmemo_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }    
}
