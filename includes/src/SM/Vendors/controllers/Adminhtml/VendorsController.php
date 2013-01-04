<?php

class SM_Vendors_Adminhtml_VendorsController extends SM_Vendors_Controller_Adminhtml_Customer {

    public function indexAction() {
        $this->_title($this->__('Vendors'))->_title($this->__('Manage Vendors'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->loadLayout();

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('smvendors/vendors_manager');

        /**
         * Add breadcrumb item
         */
        $this->_addBreadcrumb(Mage::helper('smvendors')->__('Vendors'), Mage::helper('smvendors')->__('Vendors'));
        $this->_addBreadcrumb(Mage::helper('smvendors')->__('Manage Vendors'), Mage::helper('smvendors')->__('Manage Vendors'));

        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('smvendors/adminhtml_vendor_grid')->toHtml());
    }

    protected function _initCustomer($idFieldName = 'id') {
        $this->_title($this->__('Vendors'))->_title($this->__('Manage Vendor'));

        $customerId = (int) $this->getRequest()->getParam($idFieldName);
        $customer = Mage::getModel('customer/customer');

        if (empty($customerId)) {
            if ($vendor = Mage::helper('smvendors')->getVendorLogin()) {
                $customerId = $vendor->getCustomerId();
            }
        }

        if (!empty($customerId)) {
            $customer->load($customerId);
        }


        Mage::register('current_customer', $customer);
        return $this;
    }

    public function profileAction() {
        $this->_forward('edit');
    }

    public function validateAction() {
        if ($vendor = Mage::helper('smvendors')->getVendorLogin()) {
            $customerId = $vendor->getCustomerId();
            $this->getRequest()->setParam('id', $customerId);
        }
        parent::validateAction();
    }

    protected function _setActiveMenu($menu) {
        parent::_setActiveMenu('smvendors');
    }

    /**
     * Save customer action
     */
    public function saveAction() {
        $data = $this->getRequest()->getPost();

        if ($data) {
            $redirectBack = $this->getRequest()->getParam('back', false);
            $this->_initCustomer('customer_id');

            /* @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::registry('current_customer');

            /* @var $customerForm Mage_Customer_Model_Form */
            $customerForm = Mage::getModel('customer/form');
            $customerForm->setEntity($customer)
                    ->setFormCode('adminhtml_customer')
                    ->ignoreInvisible(false)
            ;

            $formData = $customerForm->extractData($this->getRequest(), 'account');
            $errors = $customerForm->validateData($formData);

            if ($errors !== true) {
                foreach ($errors as $error) {
                    $this->_getSession()->addError($error);
                }
                $this->_getSession()->setCustomerData($data);
                $this->getResponse()->setRedirect($this->getUrl('*/vendors/edit', array('id' => $customer->getId())));
                return;
            }

            $customerForm->compactData($formData);

            // unset template data
            if (isset($data['address']['_template_'])) {
                unset($data['address']['_template_']);
            }

            $modifiedAddresses = array();
            if (!empty($data['address'])) {
                /* @var $addressForm Mage_Customer_Model_Form */
                $addressForm = Mage::getModel('customer/form');
                $addressForm->setFormCode('adminhtml_customer_address')->ignoreInvisible(false);

                foreach (array_keys($data['address']) as $index) {
                    $address = $customer->getAddressItemById($index);
                    if (!$address) {
                        $address = Mage::getModel('customer/address');
                    }

                    $requestScope = sprintf('address/%s', $index);
                    $formData = $addressForm->setEntity($address)
                            ->extractData($this->getRequest(), $requestScope);
                    $errors = $addressForm->validateData($formData);
                    if ($errors !== true) {
                        foreach ($errors as $error) {
                            $this->_getSession()->addError($error);
                        }
                        $this->_getSession()->setCustomerData($data);
                        $this->getResponse()->setRedirect($this->getUrl('*/vendors/edit', array(
                                    'id' => $customer->getId())
                                ));
                        return;
                    }

                    $addressForm->compactData($formData);

                    // Set post_index for detect default billing and shipping addresses
                    $address->setPostIndex($index);

                    if ($address->getId()) {
                        $modifiedAddresses[] = $address->getId();
                    } else {
                        $customer->addAddress($address);
                    }
                }
            }

            // default billing and shipping
            if (isset($data['account']['default_billing'])) {
                $customer->setData('default_billing', $data['account']['default_billing']);
            }
            if (isset($data['account']['default_shipping'])) {
                $customer->setData('default_shipping', $data['account']['default_shipping']);
            }
            if (isset($data['account']['confirmation'])) {
                $customer->setData('confirmation', $data['account']['confirmation']);
            }

            // not modified customer addresses mark for delete
            foreach ($customer->getAddressesCollection() as $customerAddress) {
                if ($customerAddress->getId() && !in_array($customerAddress->getId(), $modifiedAddresses)) {
                    $customerAddress->setData('_deleted', true);
                }
            }

            if (isset($data['subscription'])) {
                $customer->setIsSubscribed(true);
            } else {
                $customer->setIsSubscribed(false);
            }

            if (isset($data['account']['sendemail_store_id'])) {
                $customer->setSendemailStoreId($data['account']['sendemail_store_id']);
            }

            $isNewCustomer = $customer->isObjectNew();
//            if ($isNewCustomer) {
//                // check limit
//                $vendors = Mage::getModel('smvendors/vendor')->getCollection()->load();
//                if ($vendors->count() > 0) {
//                    $this->_getSession()->addError(
//                                Mage::helper('adminhtml')->__('You have reached the limit of vendors')
//                            );
//                    $this->getResponse()->setRedirect($this->getUrl('*/vendors'));
//                    return;                    
//                }
//            }
            try {
                $sendPassToEmail = false;
                // force new customer active
                if ($isNewCustomer) {
                    $customer->setPassword($data['account']['password']);
                    $customer->setForceConfirmed(true);
                    if ($customer->getPassword() == 'auto') {
                        $sendPassToEmail = true;
                        $customer->setPassword($customer->generatePassword());
                    }
                }

                Mage::dispatchEvent('adminhtml_customer_prepare_save', array(
                    'customer' => $customer,
                    'request' => $this->getRequest()
                ));

                $customer->save();

                // send welcome email
                if ($customer->getWebsiteId() && (isset($data['account']['sendemail']) || $sendPassToEmail)) {
                    $storeId = $customer->getSendemailStoreId();
                    if ($isNewCustomer) {
                        $customer->sendNewAccountEmail('registered', '', $storeId);
                    }
                    // confirm not confirmed customer
                    else if ((!$customer->getConfirmation())) {
                        $customer->sendNewAccountEmail('confirmed', '', $storeId);
                    }
                }

                if (!empty($data['account']['new_password'])) {
                    $newPassword = $data['account']['new_password'];
                    if ($newPassword == 'auto') {
                        $newPassword = $customer->generatePassword();
                    }
                    $customer->changePassword($newPassword);
                    $customer->sendPasswordReminderEmail();
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('The vendor has been saved.')
                );
                Mage::dispatchEvent('adminhtml_customer_save_after', array(
                    'customer' => $customer,
                    'request' => $this->getRequest()
                ));

                if ($redirectBack) {
                    $this->_redirect('*/*/edit', array(
                        'id' => $customer->getId(),
                        '_current' => true
                    ));
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setCustomerData($data);
                $this->getResponse()->setRedirect($this->getUrl('*/vendors/edit', array('id' => $customer->getId())));
            } catch (Exception $e) {
                $this->_getSession()->addException($e, Mage::helper('adminhtml')->__('An error occurred while saving the customer.'));
                $this->_getSession()->setCustomerData($data);
                $this->getResponse()->setRedirect($this->getUrl('*/vendors/edit', array('id' => $customer->getId())));
                return;
            }
        }
        if ($vendor = Mage::helper('smvendors')->getVendorLogin()) {
            $this->getResponse()->setRedirect($this->getUrl('*/vendors/profile'));
            return;
        } else {
            $this->getResponse()->setRedirect($this->getUrl('*/vendors'));
            return;
        }
    }

    protected function _isAllowed() {
        $action = strtolower($this->getRequest()->getActionName());
        if ($action == 'index') {
            return parent::_isAllowed() ? Mage::getSingleton('admin/session')->isAllowed('smvendors/vendors_manager') : false;
        } else {
            return parent::_isAllowed();
        }
    }

}

?>