<?php
require_once Mage::getBaseDir('app') . '/code/core/Mage/Adminhtml/controllers/Sales/Order/CreateController.php';
class SM_Vendors_Adminhtml_Vendors_Order_CreateController extends Mage_Adminhtml_Sales_Order_CreateController
{
    /**
     * Index page
     */
    public function indexAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Orders'))->_title($this->__('New Order'));
        $this->_initSession();
        $this->loadLayout();

        $this->_setActiveMenu('smvendors/vendors_order')
            ->renderLayout();
    }


    public function reorderAction()
    {
        if($vendor = Mage::helper('smvendors')->getVendorLogin()) {
            $this->_getSession()->clear();
            $orderId = $this->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($orderId);
            /* @var $order Mage_Sales_Model_Order */
            if (!Mage::helper('sales/reorder')->canReorder($order)) {
                return $this->_forward('noRoute');
            }
            
            if ($order->getId()) {
                $items = $order->getItemsCollection();
                foreach ($items as $key => $item) {
                    if($item->getVendorId() !== $vendor->getId()) {
                        $items->removeItemByKey($key);
                    }
                }
                
                $order->setReordered(true);
                $this->_getSession()->setUseOldShippingMethod(true);
                $this->_getOrderCreateModel()->initFromOrder($order);
            
                $this->_redirect('*/*');
            }
            else {
                $this->_redirect('*/vendors_order/');
            }
        } else {
            return parent::reorderAction();
        }
    }

    /**
     * Cancel order create
     */
    public function cancelAction()
    {
        if ($orderId = $this->_getSession()->getReordered()) {
            $this->_getSession()->clear();
            $this->_redirect('*/vendors_order/view', array(
                'order_id'=>$orderId
            ));
        } else {
            $this->_getSession()->clear();
            $this->_redirect('*/*');
        }

    }

    /**
     * Saving quote and create order
     */
    public function saveAction()
    {
        try {
            $this->_processActionData('save');
            if ($paymentData = $this->getRequest()->getPost('payment')) {
                $this->_getOrderCreateModel()->setPaymentData($paymentData);
                $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
            }

            $order = $this->_getOrderCreateModel()
                ->setIsValidate(true)
                ->importPostData($this->getRequest()->getPost('order'))
                ->createOrder();
            
            $this->_getSession()->clear();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The order has been created.'));
            $this->_redirect('*/vendors_order/view', array('order_id' => $order->getId()));
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $this->_getOrderCreateModel()->saveQuote();
            $message = $e->getMessage();
            if( !empty($message) ) {
                $this->_getSession()->addError($message);
            }
            $this->_redirect('*/*/');
        } catch (Mage_Core_Exception $e){
            $message = $e->getMessage();
            if( !empty($message) ) {
                $this->_getSession()->addError($message);
            }
            $this->_redirect('*/*/');
        }
        catch (Exception $e){
            $this->_getSession()->addException($e, $this->__('Order saving error: %s', $e->getMessage()));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Acl check for admin
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        $action = strtolower($this->getRequest()->getActionName());
        switch ($action) {
            case 'index':
                $aclResource = 'smvendors/vendors_orders/actions/create';
                break;
            case 'reorder':
                $aclResource = 'smvendors/vendors_orders/actions/reorder';
                break;
            case 'cancel':
                $aclResource = 'smvendors/vendors_orders/actions/cancel';
                break;
            case 'save':
                $aclResource = 'smvendors/vendors_orders/actions/edit';
                break;
            default:
                $aclResource = 'smvendors/vendors_orders/actions';
                break;
        }
        return Mage::getSingleton('admin/session')->isAllowed($aclResource);
    }
    
    public function loadBlockAction()
    {
        $request = $this->getRequest();
        try {
            $this->_initSession()
            ->_processData();
        }
        catch (Mage_Core_Exception $e){
            $this->_reloadQuote();
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e){
            $this->_reloadQuote();
            $this->_getSession()->addException($e, $e->getMessage());
        }
    
    
        $asJson= $request->getParam('json');
        $block = $request->getParam('block');
    
        $update = $this->getLayout()->getUpdate();
        if ($asJson) {
            $update->addHandle('adminhtml_sales_order_create_load_block_json');
        } else {
            $update->addHandle('adminhtml_sales_order_create_load_block_plain');
        }
    
        if ($block) {
            $blocks = explode(',', $block);
            if ($asJson && !in_array('message', $blocks)) {
                $blocks[] = 'message';
            }
    
            foreach ($blocks as $block) {
                switch ($block) {
                    case 'customer_grid': 
                        $update->addHandle('adminhtml_vendors_order_create_load_block_' . $block);
                        break;
                    
                    default:
                        $update->addHandle('adminhtml_sales_order_create_load_block_' . $block);
                        break;
                }
            }
        }
        $this->loadLayoutUpdates()->generateLayoutXml()->generateLayoutBlocks();
        $result = $this->getLayout()->getBlock('content')->toHtml();
        if ($request->getParam('as_js_varname')) {
            Mage::getSingleton('adminhtml/session')->setUpdateResult($result);
            $this->_redirect('*/*/showUpdateResult');
        } else {
            $this->getResponse()->setBody($result);
        }
    }    
}
