<?php

/**
 * Description of EmailController
 *
 * @author tungvm
 */
class SM_Planet_Adminhtml_InvitationController extends Mage_Adminhtml_Controller_Action {

    const XML_PATH_INVITATION_EMAIL_TEMPLATE = 'invitation/invitation_email/email_template';
    const XML_PATH_INVITATION_EMAIL_SENDER = 'invitation/invitation_email/sender_identity';

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function submitAction() {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $this->getResponse()->setRedirect($this->getUrl('*/*'));
        }
        $emails = $request->getPost('email', array());
        
        try {
            foreach ($emails as $email) {
                if($email && $email!='')
                if ($this->_sendMail($email)) {
                    $this->_getSession()->addSuccess('One email is sent to: ' . $email);
                } else {
                    $this->_getSession()->addError('Some error happended when email\'s sending to: ' . $email);
                }
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_forward('index');
    }

    protected function _sendMail($to) {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);
        $storeId = Mage::app()->getStore()->getId();
        $emailTemplate = Mage::getStoreConfig(self::XML_PATH_INVITATION_EMAIL_TEMPLATE, $storeId);
        $emailSender = Mage::getStoreConfig(self::XML_PATH_INVITATION_EMAIL_SENDER, $storeId);
        $dictionary = array();
        $result = Mage::getModel('core/email_template')
                ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
                ->sendTransactional($emailTemplate, $emailSender, $to, null, $dictionary);
        $translate->setTranslateInline(true);
        return $result->getSentSuccess();
    }

}

?>
