<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Admin
 * Date: 10/10/12
 * Time: 10:54 PM
 * To change this template use File | Settings | File Templates.
 */

class SM_Planet_Model_Override_Customer extends Mage_Customer_Model_Customer {
    public function validate()
    {
        $errors = array();
//        if (!Zend_Validate::is( trim($this->getFirstname()) , 'NotEmpty')) {
//            $errors[] = Mage::helper('customer')->__('The first name cannot be empty.');
//        }
//
//        if (!Zend_Validate::is( trim($this->getLastname()) , 'NotEmpty')) {
//            $errors[] = Mage::helper('customer')->__('The last name cannot be empty.');
//        }

        if (!Zend_Validate::is($this->getEmail(), 'EmailAddress')) {
            $errors[] = Mage::helper('customer')->__('Invalid email address "%s".', $this->getEmail());
        }

        $password = $this->getPassword();
        if (!$this->getId() && !Zend_Validate::is($password , 'NotEmpty')) {
            $errors[] = Mage::helper('customer')->__('The password cannot be empty.');
        }
        if (strlen($password) && !Zend_Validate::is($password, 'StringLength', array(6))) {
            $errors[] = Mage::helper('customer')->__('The minimum password length is %s', 6);
        }
        $confirmation = $this->getConfirmation();
        if ($password != $confirmation) {
            $errors[] = Mage::helper('customer')->__('Please make sure your passwords match.');
        }



        $entityType = Mage::getSingleton('eav/config')->getEntityType('customer');
        $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'dob');
        if ($attribute->getIsRequired() && '' == trim($this->getDob())) {
            $errors[] = Mage::helper('customer')->__('The Date of Birth is required.');
        }
        $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'taxvat');
        if ($attribute->getIsRequired() && '' == trim($this->getTaxvat())) {
            $errors[] = Mage::helper('customer')->__('The TAX/VAT number is required.');
        }
        $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'gender');
        if ($attribute->getIsRequired() && '' == trim($this->getGender())) {
            $errors[] = Mage::helper('customer')->__('Gender is required.');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }
}