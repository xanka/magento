<?php
/**
 * Date: 10/27/12
 * Time: 1:36 PM
 */

class SM_Planet_ContactController extends Mage_Core_Controller_Front_Action {
    public function writeAction() {
        $params = $this->getRequest()->getParams();

        if (!$params['vendor']) {
            Mage::getSingleton('customer/session')->addError('This product is not belong to any vendor');
            $this->_redirect('*/*');
        }

        $this->loadLayout();
        $this->renderLayout();


    }

    public function postAction() {
        $post = $this->getRequest()->getPost();

        if ( $post ) {
            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);
            try {
                $postObject = new Varien_Object();
                $postObject->setData($post);

                $error = false;

                if (!Zend_Validate::is(trim($post['name']) , 'NotEmpty')) {
                    $error = true;
                }

                if (!Zend_Validate::is(trim($post['comment']) , 'NotEmpty')) {
                    $error = true;
                }

                if (!Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
                    $error = true;
                }

//                if (!Zend_Validate::is(trim($post['vendoremail']), 'VendorEmail')) {
//                    $error = true;
//                }

                if (Zend_Validate::is(trim($post['hideit']), 'NotEmpty')) {
                    $error = true;
                }

                if ($error) {
                    throw new Exception();
                }
                $sender = array(
                    'name' => Mage::getStoreConfig('trans_email/ident_general/name'),
                    'email' =>  Mage::getStoreConfig('trans_email/ident_general/email')
                );

                $emailTemplate = Mage::getStoreConfig('smvendors_email/contact_vendor/template');
                $mailSubject = "You gor an email from buyer";
                $vars = array(
                    'customer' => $post['name'],
                    'cutomeremail' => $post['email'],
                    'content' => $post['comment']
                );

                $bcc = array(
                    'name' => 'a530921@rmqkr.net',
                    'email' => 'a530921@rmqkr.net'
                );
                $storeId = Mage::app()->getStore()->getId();
                $translate  = Mage::getSingleton('core/translate');

                $mailTemplate = Mage::getModel('core/email_template');
                /* @var $mailTemplate Mage_Core_Model_Email_Template */

                $mailTemplate->setTemplateSubject($mailSubject)->addBcc($bcc)
                    ->sendTransactional($emailTemplate ,$sender, $post['vendoremail'], 'vendor name', $vars, $storeId);



//                if (!$mailTemplate->getSentSuccess()) {
//                    throw new Exception();
//                }

                $translate->setTranslateInline(true);

                Mage::getSingleton('customer/session')->addSuccess(Mage::helper('contacts')->__('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.'));
//                $this->_redirect( "planet/contact/write/",array("vendor"=>$post['vendor']));
//                 Mage::helper("adminhtml")->getUrl("adminhtml/customemail/index/",array("vendor"=>$post['vendor']));
                $this->_redirect('*/contact/success');
                $this->loadLayout();
                $this->renderLayout();

//                return;
            } catch (Exception $e) {
                $translate->setTranslateInline(true);

                Mage::getSingleton('customer/session')->addError(Mage::helper('contacts')->__('Unable to submit your request. Please, try again later'));
                $this->_redirect('*/*/');
                return;
            }

        } else {
            $this->_redirect('*/write/');
        }

    }

    public function successAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
}