<?php
/**
 * Date: 10/25/12
 * Time: 10:42 PM
 */

class SM_Planet_Helper_Email {
    public function sendAdminWhenRejectOrder() {
        $templateId = Mage::getStoreConfig('smvendors_email/order/template_reject_to_admin');
        $mailSubject = 'Notice about order was reject';
        $sender = Array('name'  => 'Magento System',
            'email' => 'magento@magento.com');

        $email = Mage::getStoreConfig('trans_email/ident_general/email');
        $name =  Mage::getStoreConfig('trans_email/ident_general/name');

        $order = Mage::registry('order_reject');
        $currentVendor = Mage::registry('vendor_reject');
        $vars = array(
            'order' => $order ,
            'vendor' => $currentVendor ,
            'reason' => Mage::registry('reason'),
            'content' => Mage::registry('content')
        );
        $storeId = Mage::app()->getStore()->getId();

        $translate  = Mage::getSingleton('core/translate');
        Mage::getModel('core/email_template')
            ->setTemplateSubject($mailSubject)
            ->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
        $translate->setTranslateInline(true);
    }


    public function sendEmailWhenRejectOrderToCustomer($customerEmail) {
//        $customer = Mage::getModel('customer/customer')->loadByEmail($customerEmail);

        $templateId = Mage::getStoreConfig('smvendors_email/order/template_reject_to_customer');
        $mailSubject = 'Notice about order was rejected';
        $sender = Array('name'  => Mage::getStoreConfig('trans_email/ident_general/name'),
            'email' => Mage::getStoreConfig('trans_email/ident_general/email'));

        $email = $customerEmail;
        $name = 'f f';

//        $order = Mage::registry('order_reject');

        $vars = array(
//            'order' => $order
        );
        $storeId = Mage::app()->getStore()->getId();

        $translate  = Mage::getSingleton('core/translate');
        Mage::getModel('core/email_template')
            ->setTemplateSubject($mailSubject)
            ->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
        $translate->setTranslateInline(true);


    }
}