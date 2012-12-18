<?php
class SM_Vendors_Helper_Email extends Mage_Core_Helper_Abstract
{
    const TYPE_NEW = 'new';
    const TYPE_UPDATE = 'update';
    
    const ORDER_EMAIL_TEMPLATE_ID                     = 'smvendors_email/order/template_new';
    const ORDER_EMAIL_TO_VENDOR_TEMPLATE_ID           = 'smvendors_email/order/template_new_to_vendor';
    const ORDER_EMAIL_UPDATE_TEMPLATE_ID              = 'smvendors_email/order/template_update';
    const ORDER_XML_PATH_EMAIL_IDENTITY               = 'sales_email/order/identity';
    const ORDER_XML_PATH_UPDATE_EMAIL_IDENTITY        = 'sales_email/order_comment/identity';
    
    const INVOICE_EMAIL_TEMPLATE_ID                   = 'smvendors_email/invoice/template_new';
    const INVOICE_EMAIL_UPDATE_TEMPLATE_ID            = 'smvendors_email/invoice/template_update';
    const INVOICE_XML_PATH_EMAIL_IDENTITY             = 'sales_email/invoice/identity';
    const INVOICE_XML_PATH_UPDATE_EMAIL_IDENTITY      = 'sales_email/invoice_comment/identity';
    
    const SHIPMENT_EMAIL_TEMPLATE_ID                  = 'smvendors_email/shipment/template_new';
    const SHIPMENT_EMAIL_UPDATE_TEMPLATE_ID           = 'smvendors_email/shipment/template_update';
    const SHIPMENT_XML_PATH_EMAIL_IDENTITY            = 'sales_email/shipment/identity';
    const SHIPMENT_XML_PATH_UPDATE_EMAIL_IDENTITY     = 'sales_email/shipment_comment/identity';
    
    const CREDITMEMO_EMAIL_TEMPLATE_ID                = 'smvendors_email/creditmemo/template_new';
    const CREDITMEMO_EMAIL_UPDATE_TEMPLATE_ID         = 'smvendors_email/creditmemo/template_update';
    const CREDITMEMO_XML_PATH_EMAIL_IDENTITY          = 'sales_email/creditmemo/identity';
    const CREDITMEMO_XML_PATH_UPDATE_EMAIL_IDENTITY   = 'sales_email/creditmemo_comment/identity';
    
    public function sendNewOrderEmail(SM_Vendors_Model_Order $vendorOrder, 
            Mage_Sales_Model_Order $order, $notifyCustomer = true)
    {
        $data = array();
        $data['order'] = $order;
        $data['can_send_method'] = 'canSendNewOrderEmail';
        $data['template_id'] = self::ORDER_EMAIL_TEMPLATE_ID;
        $data['email_identity'] = self::ORDER_XML_PATH_EMAIL_IDENTITY;
        $data['notify_customer'] = $notifyCustomer;
        $data['target_object'] = $order;
        
        $result = $this->_sendMail($vendorOrder, $data, self::TYPE_NEW);
        
        if(!$result) {
            Mage::log("[smvendors][sendNewOrderEmail] Error when sending email. Data=\n");
            Mage::log(print_r($data, true));
        }
        
        return $result;
    }
    
    public function sendNewOrderEmailToVendor(SM_Vendors_Model_Order $vendorOrder, 
            Mage_Sales_Model_Order $order)
    {
        $data = array();
        $data['order'] = $order;
        $data['can_send_method'] = 'canSendNewOrderEmail';
        $data['template_id'] = self::ORDER_EMAIL_TO_VENDOR_TEMPLATE_ID;
        $data['email_identity'] = self::ORDER_XML_PATH_EMAIL_IDENTITY;
        $data['notify_customer'] = false;
        $data['target_object'] = $order;
        $data['acceptorder'] = Mage::helper("adminhtml")->getUrl("adminhtml/customemail/index/",array("orderid"=>$vendorOrder->getId()));
        
        $result = $this->_sendMail($vendorOrder, $data, self::TYPE_NEW);
        
        if(!$result) {
            Mage::log("[smvendors][sendNewOrderEmailToVendor] Error when sending email. Data=\n");
            Mage::log(print_r($data, true));
        }
        
        return $result;
    }
    
    public function sendUpdateOrderEmail(SM_Vendors_Model_Order $vendorOrder, 
            Mage_Sales_Model_Order $order = null, $notifyCustomer = true, $comment = '')
    {
        $data = array();
        $data['order'] = $order;
        $data['can_send_method'] = 'canSendOrderCommentEmail';
        $data['template_id'] = self::ORDER_EMAIL_UPDATE_TEMPLATE_ID;
        $data['email_identity'] = self::ORDER_XML_PATH_UPDATE_EMAIL_IDENTITY;
        $data['notify_customer'] = $notifyCustomer;
        $data['template_params'] = array('comment' => $comment);
        
        $result = $this->_sendMail($vendorOrder, $data, self::TYPE_UPDATE);
        
        if(!$result) {
            Mage::log("[smvendors][sendUpdateOrderEmail] Error when sending email. Data=\n");
            Mage::log(print_r($data, true));
        }
        
        return $result;
    }
    
    public function sendNewInvoiceEmail(SM_Vendors_Model_Order $vendorOrder, 
            Mage_Sales_Model_Order_Invoice $invoice, $notifyCustomer = true, $comment = '')
    {
        $data = array();
        $data['order'] = $invoice->getOrder();
        $data['can_send_method'] = 'canSendNewInvoiceEmail';
        $data['template_id'] = self::INVOICE_EMAIL_TEMPLATE_ID;
        $data['email_identity'] = self::INVOICE_XML_PATH_EMAIL_IDENTITY;
        $data['notify_customer'] = $notifyCustomer;
        $data['target_object'] = $invoice;
        $data['template_params'] = array('comment' => $comment, 'invoice' => $invoice);
        
        $result = $this->_sendMail($vendorOrder, $data, self::TYPE_NEW);
        
        if(!$result) {
            Mage::log("[smvendors][sendNewInvoiceEmail] Error when sending email. Data=\n");
            Mage::log(print_r($data, true));
        }
        
        return $result;        
    }
    
    public function sendUpdateInvoiceEmail(SM_Vendors_Model_Order $vendorOrder, 
            Mage_Sales_Model_Order_Invoice $invoice, $notifyCustomer = true, $comment = '')
    {
        $data = array();
        $data['order'] = $invoice->getOrder();
        $data['can_send_method'] = 'canSendInvoiceCommentEmail';
        $data['template_id'] = self::INVOICE_EMAIL_UPDATE_TEMPLATE_ID;
        $data['email_identity'] = self::INVOICE_XML_PATH_UPDATE_EMAIL_IDENTITY;
        $data['notify_customer'] = $notifyCustomer;
        $data['target_object'] = $invoice;
        $data['template_params'] = array('comment' => $comment, 'invoice' => $invoice);
        
        $result = $this->_sendMail($vendorOrder, $data, self::TYPE_UPDATE);
        
        if(!$result) {
            Mage::log("[smvendors][sendUpdateInvoiceEmail] Error when sending email. Data=\n");
            Mage::log(print_r($data, true));
        }
        
        return $result;        
    }
    
    public function sendNewShipmentEmail(SM_Vendors_Model_Order $vendorOrder, 
            Mage_Sales_Model_Order_Shipment $shipment, $notifyCustomer = true, $comment = '')
    {
        $data = array();
        $data['order'] = $shipment->getOrder();
        $data['can_send_method'] = 'canSendNewShipmentEmail';
        $data['template_id'] = self::SHIPMENT_EMAIL_TEMPLATE_ID;
        $data['email_identity'] = self::SHIPMENT_XML_PATH_EMAIL_IDENTITY;
        $data['notify_customer'] = $notifyCustomer;
        $data['target_object'] = $shipment;
        $data['template_params'] = array('comment' => $comment, 'shipment' => $shipment);
        
        $result = $this->_sendMail($vendorOrder, $data, self::TYPE_NEW);
        
        if(!$result) {
            Mage::log("[smvendors][sendNewShipmentEmail] Error when sending email. Data=\n");
            Mage::log(print_r($data, true));
        }
        
        return $result;        
    }
    
    public function sendUpdateShipmentEmail(SM_Vendors_Model_Order $vendorOrder, 
            Mage_Sales_Model_Order_Shipment $shipment, $notifyCustomer = true, $comment = '')
    {
        $data = array();
        $data['order'] = $shipment->getOrder();
        $data['can_send_method'] = 'canSendShipmentCommentEmail';
        $data['template_id'] = self::SHIPMENT_EMAIL_UPDATE_TEMPLATE_ID;
        $data['email_identity'] = self::SHIPMENT_XML_PATH_UPDATE_EMAIL_IDENTITY;
        $data['notify_customer'] = $notifyCustomer;
        $data['target_object'] = $shipment;
        $data['template_params'] = array('comment' => $comment, 'shipment' => $shipment);
        
        $result = $this->_sendMail($vendorOrder, $data, self::TYPE_UPDATE);
        
        if(!$result) {
            Mage::log("[smvendors][sendUpdateShipmentEmail] Error when sending email. Data=\n");
            Mage::log(print_r($data, true));
        }
        
        return $result;        
    }
    
    public function sendNewCreditMemoEmail(SM_Vendors_Model_Order $vendorOrder, 
            Mage_Sales_Model_Order_Creditmemo $creditmemo, $notifyCustomer = true, $comment = '')
    {
        $data = array();
        $data['order'] = $creditmemo->getOrder();
        $data['can_send_method'] = 'canSendNewCreditmemoEmail';
        $data['template_id'] = self::CREDITMEMO_EMAIL_TEMPLATE_ID;
        $data['email_identity'] = self::CREDITMEMO_XML_PATH_EMAIL_IDENTITY;
        $data['notify_customer'] = $notifyCustomer;
        $data['target_object'] = $creditmemo;
        $data['template_params'] = array('comment' => $comment, 'creditmemo' => $creditmemo);
        
        $result = $this->_sendMail($vendorOrder, $data, self::TYPE_NEW);
        
        if(!$result) {
            Mage::log("[smvendors][sendNewCreditMemoEmail] Error when sending email. Data=\n");
            Mage::log(print_r($data, true));
        }
        
        return $result;        
    }
    
    public function sendUpdateCreditmemoEmail(SM_Vendors_Model_Order $vendorOrder, 
            Mage_Sales_Model_Order_Creditmemo $creditmemo, $notifyCustomer = true, $comment = '')
    {
        $data = array();
        $data['order'] = $creditmemo->getOrder();
        $data['can_send_method'] = 'canSendCreditmemoCommentEmail';
        $data['template_id'] = self::CREDITMEMO_EMAIL_UPDATE_TEMPLATE_ID;
        $data['email_identity'] = self::CREDITMEMO_XML_PATH_UPDATE_EMAIL_IDENTITY;
        $data['notify_customer'] = $notifyCustomer;
        $data['target_object'] = $creditmemo;
        $data['template_params'] = array('comment' => $comment, 'creditmemo' => $creditmemo);
        
        $result = $this->_sendMail($vendorOrder, $data, self::TYPE_UPDATE);
        
        if(!$result) {
            Mage::log("[smvendors][sendUpdateCreditmemoEmail] Error when sending email. Data=\n");
            Mage::log(print_r($data, true));
        }
        
        return $result;        
    }
    
    protected function _sendMail(SM_Vendors_Model_Order $vendorOrder, $data, $type = self::TYPE_NEW) {
        if($vendorOrder === null && isset($data['target_object']) && isset($data['order'])) {
            $object = $data['target_object'];
            $order = $data['order'];
            if($object->getVendorId()) {
                $vendorOrder = Mage::getModel('smvendors/order')->getByOriginOrderId($order->getId(), $object->getVendorId());
                $vendorOrder->setOriginalOrder($order);
            }
        }
        
        if(!$vendorOrder){
            Mage::log("[smvendors][_sendMail] Vendor order is not exist or has been deleted1.");
            return false;
        }
        
        $vendor = $vendorOrder->getVendor();
        if(!$vendor->getId()) {
            Mage::log("[smvendors][_sendMail] Vendor is not exist or has been deleted2.");
            return false;
        }
        
        $order = null;
        if(isset($data['order']) && $data['order'] instanceof  Mage_Sales_Model_Order) {
            $order = $data['order'];
        } else {
            $order = $vendorOrder->getOriginalOrder();
        }
        
        if(!$order->getId()) {
            Mage::log("[smvendors][_sendMail] Order is not exist or has been deleted.");
            return false;
        }
        
        $storeId = $order->getStore()->getId();
        
        $canSendMethod = $data['can_send_method'];
        if($canSendMethod && method_exists(Mage::helper('sales'), $canSendMethod)) {
            if (!Mage::helper('sales')->$canSendMethod($storeId)) {
                return false;
            }
        }
        
        $paymentBlockHtml = '';
        if($type == self::TYPE_NEW) {
            $appEmulation = Mage::getSingleton('core/app_emulation');
            $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
            
            try {
                // Retrieve specified view block from appropriate design package (depends on emulated store)
                $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                    ->setIsSecureMode(true);
                $paymentBlock->getMethod()->setStore($storeId);
                $paymentBlockHtml = $paymentBlock->toHtml();
            } catch (Exception $exception) {
                // Stop store emulation process
                $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                throw $exception;
            }
            
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        }
        
        // Retrieve corresponding email template id and customer name
        // HiepHM: Use same template for Guest
        if ($order->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig($data['template_id'], $storeId);
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $templateId = Mage::getStoreConfig($data['template_id'], $storeId);
            $customerName = $order->getCustomerName();
        }
        
        $mailer = Mage::getModel('core/email_template_mailer');
        if (isset($data['notify_customer']) && $data['notify_customer']) {
            $emailInfo = Mage::getModel('core/email_info');
            $emailInfo->addTo($order->getCustomerEmail(), $customerName);
            $mailer->addEmailInfo($emailInfo);
        }
        
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($vendor->getEmail());
        $mailer->addEmailInfo($emailInfo);
        
        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig($data['email_identity'], $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        
        $templateParams = array(
                'order'   => $order,
                'billing' => $order->getBillingAddress(),
                'vendor'  => $vendor,
                'vendor_order' => $vendorOrder ,
                'link_reject' => Mage::helper("adminhtml")->getUrl("adminhtml/customemail/index/",array("order_id"=>$vendorOrder->getOrderId(),"customer" => $order->getCustomerEmail() ))
                );
        if($type == self::TYPE_NEW) {
            $templateParams['payment_html'] = $paymentBlockHtml;
        }
        
        if(isset($data['template_params']) && is_array($data['template_params'])) {
            $templateParams = array_merge($templateParams, $data['template_params']);
        }
        
        $mailer->setTemplateParams($templateParams);
        $mailer->send();
        
        if($type == self::TYPE_NEW && isset($data['target_object'])) {
            $object = $data['target_object'];
            $object->setEmailSent(true);
            $object->getResource()->saveAttribute($object, 'email_sent');
        }
        
        return true;        
    }


    public function sendAdminWhenVendorRegister($customer) {
        $templateId = Mage::getStoreConfig('customer/create_account/notice_admin_about_signup_template');
        $mailSubject = "Notice about New Vendor SignUp";
        $sender = Array('name'  => Mage::getStoreConfig('trans_email/ident_general/name'),
            'email' => Mage::getStoreConfig('trans_email/ident_general/email'));

        $email = Mage::getStoreConfig('smvendors_email/admin_email/email');
        $name = Mage::getStoreConfig('smvendors_email/admin_email/name');

        $vars = array(
            'customer' => $customer
        );

        $storeId = Mage::app()->getStore()->getId();

        $translate  = Mage::getSingleton('core/translate');
        Mage::getModel('core/email_template')
            ->setTemplateSubject($mailSubject)
            ->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
        $translate->setTranslateInline(true);
    }
}