<?php
require_once Mage::getBaseDir('app') . '/code/core/Mage/Adminhtml/controllers/Sales/Order/ShipmentController.php';
class SM_Vendors_Adminhtml_Vendors_Order_ShipmentController extends Mage_Adminhtml_Sales_Order_ShipmentController
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('smvendors/vendors_shipments');
    }
    
    protected function _initAction()
    {
        $this->loadLayout()
        ->_setActiveMenu('smvendors/vendors_shipments')
        ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
        ->_addBreadcrumb($this->__('Shipments'),$this->__('Shipments'));
        return $this;
    }    
    
    /**
     * Initialize shipment model instance
     *
     * @return Mage_Sales_Model_Order_Shipment|bool
     */
    protected function _initShipment()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Shipments'));
    
        $shipment = false;
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if ($shipmentId) {
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
            
            /**
             * Load vendor order
             */
            if($vendor = Mage::helper('smvendors')->getVendorLogin()) {
                $vendorOrder = Mage::getModel('smvendors/order')->getByOriginOrderId($shipment->getOrder()->getId(), $vendor->getId());
                if(!$vendorOrder->getId()) {
                    $this->_getSession()->addError($this->__('This vendor order no longer exists.'));
                    $this->_redirect('*/*/');
                    $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                    return false;
                }
            
                Mage::register('vendor_order', $vendorOrder);
            }
        } elseif ($orderId) {
            $order      = Mage::getModel('sales/order')->load($orderId);

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
             * Check shipment is available to create separate from invoice
             */
            if ($order->getForcedDoShipmentWithInvoice()) {
                $this->_getSession()->addError($this->__('Cannot do shipment for the order separately from invoice.'));
                return false;
            }
            /**
             * Check shipment create availability
             */
            if (!$order->canShip()) {
                $this->_getSession()->addError($this->__('Cannot do shipment for the order.'));
                return false;
            }
            $savedQtys = $this->_getItemQtys();
            $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);
    
            $tracks = $this->getRequest()->getPost('tracking');
            if ($tracks) {
                foreach ($tracks as $data) {
                    if (empty($data['number'])) {
                        Mage::throwException($this->__('Tracking number cannot be empty.'));
                    }
                    $track = Mage::getModel('sales/order_shipment_track')
                    ->addData($data);
                    $shipment->addTrack($track);
                }
            }
        }
    
        Mage::register('current_shipment', $shipment);
        return $shipment;
    }

    /**
     * Shipments grid
     */
    public function indexAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Shipments'));
    
        $this->_initAction()
        ->_addContent($this->getLayout()->createBlock('smvendors/adminhtml_sales_shipment'))
        ->renderLayout();
    }

    /**
     * Shipment create page
     */
    public function newAction()
    {
        if ($shipment = $this->_initShipment()) {
            $this->_title($this->__('New Shipment'));
    
            $comment = Mage::getSingleton('adminhtml/session')->getCommentText(true);
            if ($comment) {
                $shipment->setCommentText($comment);
            }
    
            $this->loadLayout()
            ->_setActiveMenu('smvendors/vendors_orders')
            ->renderLayout();
        } else {
            $this->_redirect('*/vendors_order/view', array('order_id'=>$this->getRequest()->getParam('order_id')));
        }
    }

    /**
     * Save shipment
     * We can save only new shipment. Existing shipments are not editable
     *
     * @return null
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('shipment');
        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }
    
        try {
            $shipment = $this->_initShipment();
            if (!$shipment) {
                $this->_forward('noRoute');
                return;
            }
    
            $shipment->register();
            $comment = '';
            if (!empty($data['comment_text'])) {
                $shipment->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                );
                if (isset($data['comment_customer_notify'])) {
                    $comment = $data['comment_text'];
                }
            }
    
            if (!empty($data['send_email'])) {
                $shipment->setEmailSent(true);
            }
    
            $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
            $responseAjax = new Varien_Object();
            $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];
            if ($isNeedCreateLabel) {
                if ($this->_createShippingLabel($shipment)) {
                    $this->_getSession()
                    ->addSuccess($this->__('The shipment has been created. The shipping label has been created.'));
                    $responseAjax->setOk(true);
                }
            } else {
                $this->_getSession()
                ->addSuccess($this->__('The shipment has been created.'));
            }
            $this->_saveShipment($shipment);
            //$shipment->sendEmail(!empty($data['send_email']), $comment);
            Mage::helper('smvendors/email')->sendNewShipmentEmail(Mage::registry('vendor_order'), $shipment, !empty($data['send_email']), $comment);
            Mage::getSingleton('adminhtml/session')->getCommentText(true);
        } catch (Mage_Core_Exception $e) {
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage($e->getMessage());
            } else {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage(Mage::helper('sales')->__('An error occurred while creating shipping label.'));
            } else {
                $this->_getSession()->addError($this->__('Cannot save shipment.'));
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }
    
        }
        if ($isNeedCreateLabel) {
            $this->getResponse()->setBody($responseAjax->toJson());
        } else {
            $this->_redirect('*/vendors_order/view', array('order_id' => $shipment->getOrderId()));
        }
    }

    /**
     * Save shipment and order in one transaction
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return Mage_Adminhtml_Sales_Order_ShipmentController
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
        ->addObject($shipment)
        ->addObject($shipment->getOrder());
        
        // HiepHM Process vendor order
        if($vendorOrder = Mage::registry('vendor_order')) {
            /* @var $vendorOrder SM_Vendors_Model_Order */
            $vendorOrder->registerShipment($shipment);
            $transactionSave->addObject($vendorOrder);
        }
        
        $transactionSave->save();
    
        return $this;
    }

    /**
     * Shipment information page
     */
    public function viewAction()
    {
        if ($this->_initShipment()) {
            $this->_title($this->__('View Shipment'));
    
            $this->loadLayout();
            $this->getLayout()->getBlock('sales_shipment_view')
            ->updateBackButtonUrl($this->getRequest()->getParam('come_from'));
            $this->_setActiveMenu('smvendors/vendors_orders')
            ->renderLayout();
        } else {
            $this->_forward('noRoute');
        }
    }

    /**
     * Add comment to shipment history
     */
    public function addCommentAction()
    {
        try {
            $this->getRequest()->setParam(
                    'shipment_id',
                    $this->getRequest()->getParam('id')
            );
            $data = $this->getRequest()->getPost('comment');
            if (empty($data['comment'])) {
                Mage::throwException($this->__('Comment text field cannot be empty.'));
            }
            $shipment = $this->_initShipment();
            $shipment->addComment(
                    $data['comment'],
                    isset($data['is_customer_notified']),
                    isset($data['is_visible_on_front'])
            );
            //$shipment->sendUpdateEmail(!empty($data['is_customer_notified']), $data['comment']);
            Mage::helper('smvendors/email')->sendUpdateShipmentEmail(Mage::registry('vendor_order'), $shipment, $data['is_customer_notified'], $data['comment']);
            $shipment->save();
    
            $this->loadLayout(false);
            $response = $this->getLayout()->getBlock('shipment_comments')->toHtml();
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
     * Send email with shipment data to customer
     */
    public function emailAction()
    {
        try {
            $shipment = $this->_initShipment();
            if ($shipment) {
//                 $shipment->sendEmail(true)
//                 ->setEmailSent(true)
//                 ->save();
                Mage::helper('smvendors/email')->sendNewShipmentEmail(Mage::registry('vendor_order'), $shipment);
                $historyItem = Mage::getResourceModel('sales/order_status_history_collection')
                ->getUnnotifiedForInstance($shipment, Mage_Sales_Model_Order_Shipment::HISTORY_ENTITY_NAME);
                if ($historyItem) {
                    $historyItem->setIsCustomerNotified(1);
                    $historyItem->save();
                }
                $this->_getSession()->addSuccess($this->__('The shipment has been sent.'));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot send shipment information.'));
        }
        $this->_redirect('*/*/view', array(
                'shipment_id' => $this->getRequest()->getParam('shipment_id')
        ));
    }

    /**
     * Batch print shipping labels for whole shipments.
     * Push pdf document with shipping labels to user browser
     *
     * @return null
     */
    public function massPrintShippingLabelAction()
    {
        $request = $this->getRequest();
        $ids = $request->getParam('order_ids');
        $createdFromOrders = !empty($ids);
        $shipments = null;
        $labelsContent = array();
        switch ($request->getParam('massaction_prepare_key')) {
            case 'shipment_ids':
                $ids = $request->getParam('shipment_ids');
                array_filter($ids, 'intval');
                if (!empty($ids)) {
                    $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                    ->addFieldToFilter('entity_id', array('in' => $ids));
                }
                break;
            case 'order_ids':
                $ids = $request->getParam('order_ids');
                array_filter($ids, 'intval');
                if (!empty($ids)) {
                    $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                    ->setOrderFilter(array('in' => $ids));
                }
                break;
        }
    
        if ($shipments && $shipments->getSize()) {
            foreach ($shipments as $shipment) {
                $labelContent = $shipment->getShippingLabel();
                if ($labelContent) {
                    $labelsContent[] = $labelContent;
                }
            }
        }
    
        if (!empty($labelsContent)) {
            $outputPdf = $this->_combineLabelsPdf($labelsContent);
            $this->_prepareDownloadResponse('ShippingLabels.pdf', $outputPdf->render(), 'application/pdf');
            return;
        } else {
            $createdFromPartErrorMsg = $createdFromOrders ? 'orders' : 'shipments';
            $this->_getSession()
            ->addError(Mage::helper('sales')->__('There are no shipping labels related to selected %s.', $createdFromPartErrorMsg));
        }
        if ($createdFromOrders) {
            $this->_redirect('*/vendors_order/index');
        } else {
            $this->_redirect('*/vendors_order_shipment/index');
        }
    }

    /**
     * Export shipment grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'shipments.csv';
        $grid       = $this->getLayout()->createBlock('smvendors/adminhtml_sales_shipment_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }
    
    /**
     *  Export shipment grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName   = 'shipments.xml';
        $grid       = $this->getLayout()->createBlock('smvendors/adminhtml_sales_shipment_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }    
}
