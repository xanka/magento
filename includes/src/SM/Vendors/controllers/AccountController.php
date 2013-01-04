<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Customer
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer account controller
 *
 * @category   Mage
 * @package    Mage_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
require_once("Mage/Customer/controllers/AccountController.php");
class SM_Vendors_AccountController extends Mage_Customer_AccountController
{	
	public function createAction()
    {

        if ($this->_getSession()->isLoggedIn()) {
        	$customer = Mage::helper('customer')->getCustomer();
        	if($customer->getCustomerType()== 'vendor'){
            	$this->_redirect('*/*');
            	return;
        	}
        	
        }

        
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }
    
    /**
     * Create customer account action
     */
    public function createPostAction()
    {
        $session = $this->_getSession();
    	if ($session->isLoggedIn()) {
        	$customer = Mage::helper('customer')->getCustomer();
        	if($customer->getCustomerType()== 'vendor'){
            	$this->_redirect('*/*');
            	return;
        	}
        	$customer->setData('vendor_contact_email',$customer->getEmail());
        	$customer->setData('vendor_name',$this->getRequest()->getPost('vendor_name'));
        	$customer->setCustomerType('vendor');
        	$customer->setData('vendor_status',0);
        	$passwordHash = $customer->getPasswordHash();
        	Mage::register('current_customer',$customer);
        }
        
        $session->setEscapeMessages(true); // prevent XSS injection in user input
        if ($this->getRequest()->isPost()) {
            $errors = array();

            if (!$customer = Mage::registry('current_customer')) {
                $customer = Mage::getModel('customer/customer')->setId(null);
            }
            // print_r($customer->getData());
            //die($customer->getId());

            /* @var $customerForm Mage_Customer_Model_Form */
            $customerForm = Mage::getModel('customer/form');
            $customerForm->setFormCode('customer_account_create')
                ->setEntity($customer);
            $customerData = $customerForm->extractData($this->getRequest());

            if ($this->getRequest()->getParam('is_subscribed', false)) {
                $customer->setIsSubscribed(1);
            }

            /**
             * Initialize customer group id
             */
            $customer->getGroupId();

            if ($this->getRequest()->getPost('create_address')) {
                /* @var $address Mage_Customer_Model_Address */
                $address = Mage::getModel('customer/address');
                /* @var $addressForm Mage_Customer_Model_Form */
                $addressForm = Mage::getModel('customer/form');
                $addressForm->setFormCode('customer_register_address')
                    ->setEntity($address);

                $addressData    = $addressForm->extractData($this->getRequest(), 'address', false);
                $addressErrors  = $addressForm->validateData($addressData);
                if ($addressErrors === true) {
                    $address->setId(null)
                        ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                        ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
                    $addressForm->compactData($addressData);
                    $customer->addAddress($address);

                    $addressErrors = $address->validate();
                    if (is_array($addressErrors)) {
                        $errors = array_merge($errors, $addressErrors);
                    }
                } else {
                    $errors = array_merge($errors, $addressErrors);
                }
            }
            
           $params = $this->getRequest()->getParams();
            try {
//                $customerErrors = $customerForm->validateData($customerData);
                $customerErrors = true;
                if ($customerErrors !== true) {
                    $errors = array_merge($customerErrors, $errors);
                } else {
                    $customerForm->compactData($customerData);
                    $customer->addData($this->getRequest()->getPost());
                    if (!$session->isLoggedIn() && !empty($params['fullname'])) {
						// Get full name , split into first name last name
	                    $fullname = $params['fullname'];
	                    $_fullname = explode(' ',$fullname);
	                    if(count($_fullname) > 0)  {
	                        $lastName = $_fullname[count($_fullname)-1];
	                        $firstName = substr($fullname,0,strlen($fullname) - strlen($lastName) - 1);
	                    }
	
	                    else {
	                        $lastName = $fullname;
	                        $firstName = '';
	                    }	
	                    $customer->setData('fullname',$fullname);
	                    $customer->setFirstname($firstName);
	                    $customer->setLastname($lastName);
                    }
                    //print_r($customer->getData());die;

                    if($this->getRequest()->getPost('password')){
                    	$customer->setPassword($this->getRequest()->getPost('password'));
                    	$customer->setConfirmation($this->getRequest()->getPost('confirmation'));
                    }
                    $customerErrors = $customer->validate();
                    if (is_array($customerErrors)) {
                        $errors = array_merge($customerErrors, $errors);
                    }
                }
                $validationResult = count($errors) == 0;

                if (true === $validationResult) {
                	
					if($customer->getPasswordHash() =='' AND !empty($passwordHash)){
						$customer->setPasswordHash($passwordHash);
					}
					
					
					
                    $customer->save();

                    Mage::dispatchEvent('customer_register_success',
                        array('account_controller' => $this, 'customer' => $customer)
                    );

                    if ($customer->isConfirmationRequired()) {
                        $customer->sendNewAccountEmail(
                            'confirmation',
                            $session->getBeforeAuthUrl(),
                            Mage::app()->getStore()->getId()
                        );
                        $session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())));
                        $this->_redirectSuccess(Mage::getUrl('customer/account/login', array('_secure'=>true)));
                        return;
                    } else {
                        echo "teleconve";
                        $session->setCustomerAsLoggedIn($customer);
                        $url = $this->_welcomeCustomer($customer);
                        //$this->_redirectSuccess($url);
                    	$this->_redirectSuccess(Mage::getUrl('customer/account/index', array('_secure'=>true)));
                        return;
                    }
                } else {
                    $session->setCustomerFormData($this->getRequest()->getPost());
                    if (is_array($errors)) {
                        foreach ($errors as $errorMessage) {
                            $session->addError($errorMessage);
                        }
                    } else {
                        $session->addError($this->__('Invalid customer data'));
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $session->setCustomerFormData($this->getRequest()->getPost());
                if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                    $url = Mage::getUrl('customer/account/forgotpassword');
                    $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
                    $session->setEscapeMessages(false);
                } else {
                    $message = $e->getMessage();
                }
                $session->addError($message);
            } catch (Exception $e) {
                $session->setCustomerFormData($this->getRequest()->getPost())
                    ->addException($e, $this->__('Cannot save the customer.'));
            }
        }

        $this->_redirectError(Mage::getUrl('*/*/create', array('_secure' => true)));
    }
}
