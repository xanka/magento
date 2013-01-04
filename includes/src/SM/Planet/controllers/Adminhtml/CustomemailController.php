<?php

/**
 * Description of EmailController
 *
 * @author tungvm
 */
require_once Mage::getBaseDir('app') . '/code/local/SM/Vendors/controllers/Adminhtml/Vendors/OrderController.php';

class SM_Planet_Adminhtml_CustomemailController extends SM_Vendors_Adminhtml_Vendors_OrderController
{

    /*
     * Show Page for custom and send rejected email
     * 
     * 
     */

    // Disable secret key check
    protected function _validateSecretKey() {
        return true;
    }

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    //Need to code again :hieunt
    public function submitAction() {
        $params = $this->getRequest()->getParams();

        if($params['decision'] == 0) {
            Mage::getSingleton('core/session')->addSuccess('Order was accepted now, you can go to order manager to manage this order');
            $this->loadLayout();
            $this->renderLayout();
            return;
        }


        try{

            $order = Mage::getModel('smvendors/order')->load($params['order_id']);
            // $customer = Mage::getModel('customer/customer')->loadByEmail($params['customer_email']);
        }
        catch(Exception $e) {
            Mage::getSingleton('core/session')->addError('Order not found or customer not found');
            $this->loadLayout();
            $this->renderLayout();
            return;
        }

        if ($order->getState() == SM_Vendors_Model_Order::STATUS_CLOSED || $order->getState() == SM_Vendors_Model_Order::STATUS_CANCELED ) {
            Mage::getSingleton('core/session')->addError('Order was closed or canceled already');
            $this->loadLayout();
            $this->renderLayout();
            return;
        }
//        if(!$order->isCanceled() && !$order->canCancel()) {
//            Mage::getSingleton('core/session')->addError('This order can not cancel');
//            $this->loadLayout();
//            $this->renderLayout();
//            return;
//        }
//        $order->setData('status',SM_Vendors_Model_Order::STATUS_CANCELED);
        self::cancelAction();



        $order->save();

        // Begin Send Email
        $currentVendor = Mage::helper('smvendors')->getCurrentVendor() ? Mage::helper('smvendors')->getCurrentVendor() : 'admin';

        Mage::register('vendor_reject',$currentVendor);
        Mage::register('order_reject',$order);
        Mage::register('reason',$params['reason']);
        Mage::register('content',$params['help_text']);

        try{
            Mage::helper('planet/email')->sendAdminWhenRejectOrder();
            Mage::helper('planet/email')->sendEmailWhenRejectOrderToCustomer($params['customer_email']);

            $message = $this->__('Sent Email Success!.');
            Mage::getSingleton('core/session')->addSuccess($message);
        }
        catch(Exception $e) {
            Mage::getSingleton('core/session')->addError('Cannot sent email');
            Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('*/*'));
        }



        $this->loadLayout();
        $this->renderLayout();
    }

}

?>
