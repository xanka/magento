<?php

class SM_Vendors_Adminhtml_Vendors_CustomerController extends SM_Vendors_Controller_Adminhtml_Customer {

    public function massAssignVendorGroupAction() {
        $customersIds = $this->getRequest()->getParam('customer');
        if (!is_array($customersIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select customer(s).'));
        } else {
            try {
                $vendorGroup = $this->getRequest()->getParam('vendor_group');

                $groupOptions = Mage::getSingleton('eav/config')
                                ->getAttribute('customer', 'vendor_customer_group')
                                ->getSource()->getAllOptions();
                foreach ($customersIds as $customerId) {
                    $customer = Mage::getModel('customer/customer')->load($customerId);

                    $currentGroup = $customer->getData('vendor_customer_group');

                    if ($currentGroup) {
                        $currentGroup = explode(',', $currentGroup);
                        // Remove blank value
                        foreach ($currentGroup as $key => $item) {
                            if (!$item) {
                                unset($currentGroup[$key]);
                            }
                        }
                    }

                    $vendorGroups = array();

                    foreach ($groupOptions as $option) {
                        if (in_array($option['value'], $currentGroup)) {
                            $vendorGroups[] = $option['value'];
                        }
                    }

                    $hiddenGroups = array_diff($currentGroup, $vendorGroups);
                    //$hiddenGroups[] = $vendorGroup;

                    $currentGroup = array_merge($vendorGroups, $hiddenGroups);
                    $currentGroup[] = $vendorGroup;

                    $currentGroup = array_unique($currentGroup);

                    $customer->setData('vendor_customer_group', implode(',', $currentGroup));

                    $customer->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d record(s) were updated.', count($customersIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('adminhtml/customer/index');
    }

    public function saveCustomerAction() {
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
                $this->getResponse()->setRedirect($this->getUrl('*/customer/edit', array('id' => $customer->getId())));
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
                        $this->getResponse()->setRedirect($this->getUrl('*/customer/edit', array(
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



            if (isset($data['subscription'])) {
                $customer->setIsSubscribed(true);
            } else {
                $customer->setIsSubscribed(false);
            }

            if (isset($data['account']['sendemail_store_id'])) {
                $customer->setSendemailStoreId($data['account']['sendemail_store_id']);
            }

            $isNewCustomer = $customer->isObjectNew();
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
                        Mage::helper('adminhtml')->__('The customer has been saved.')
                );
                Mage::dispatchEvent('adminhtml_customer_save_after', array(
                    'customer' => $customer,
                    'request' => $this->getRequest()
                ));

                if ($redirectBack) {
                    $this->_redirect('*/customer/edit', array(
                        'id' => $customer->getId(),
                        '_current' => true
                    ));
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->setCustomerData($data);
                $this->getResponse()->setRedirect($this->getUrl('*/customer/edit', array('id' => $customer->getId())));
            } catch (Exception $e) {
                $this->_getSession()->addException($e, Mage::helper('adminhtml')->__('An error occurred while saving the customer.'));
                $this->_getSession()->setCustomerData($data);
                $this->getResponse()->setRedirect($this->getUrl('*/customer/edit', array('id' => $customer->getId())));
                return;
            }
        }
        $this->getResponse()->setRedirect($this->getUrl('*/customer'));
    }

}
