<?php

/**
 * Date: 10/16/12
 * Time: 12:53 AM
 */
class SM_Planet_Model_Observer {

    public function hookVendorEditTabPrepareForm($observer) {

        $fieldset = $observer->getFieldset();
        $customer = Mage::registry('current_customer');

        $countryList = Mage::getResourceModel('directory/country_collection')
                ->loadData()
                ->toOptionArray(false);
        if ($this->isElementAllow('vendor_delivery_area')) {
            if ($vendorId = $customer->getVendorId()) {
                $fieldset->addField('vendor_delivery_area', 'multiselect', array(
                    'label' => Mage::helper('planet')->__('Delivery Area'),
                    'name' => 'vendor[deliveryarea][]',
                    'value' => 'deliveryarea',
                    'values' => $countryList
                ));
            }
        }

        if ($this->isElementAllow('return_page')) {
            if ($vendorId = $customer->getVendorId()) {
                $fieldset->addField('return_page', 'textarea', array(
                    'label' => Mage::helper('planet')->__('Returns Policy'),
                    'name' => 'vendor[return_page]',
                    'note' => 'This will be displayed to your customers',
                ));
            }
        }

        if ($this->isElementAllow('delivery_page')) {
            if ($vendorId = $customer->getVendorId()) {
                $vendor = Mage::getModel('smvendors/vendor')->load($vendorId);
                $vendorLink = $vendor->getVendorUrl();
                $fieldset->addField('delivery_page', 'textarea', array(
                    'label' => Mage::helper('planet')->__('Delivery Charges'),
                    'name' => 'vendor[delivery_page]',
                    'note' => 'This will be displayed to your customers<br/><span><a href="' . $vendorLink . '">' . $vendorLink . '</a></span><br/>Now see how your company information looks on the Planet V website - Merchant List',
                ));
            }
        }

        if ($this->isElementAllow('vendor_reg_number')) {
            if ($vendorId = $customer->getVendorId()) {
                $fieldset->addField('vendor_reg_number', 'text', array(
                    'label' => Mage::helper('planet')->__('Register Number'),
                    'name' => 'vendor[vendor_reg_number]'
                ));
            }
        }

        if ($this->isElementAllow('vendor_company_type')) {
            if ($vendorId = $customer->getVendorId()) {
                $fieldset->addField('vendor_company_type', 'text', array(
                    'label' => Mage::helper('planet')->__('Company Type'),
                    'name' => 'vendor[vendor_company_type]'
                ));
            }
        }

        if ($this->isElementAllow('vendor_address')) {
            if ($vendorId = $customer->getVendorId()) {
                $fieldset->addField('vendor_address', 'text', array(
                    'label' => Mage::helper('planet')->__('Address'),
                    'name' => 'vendor[vendor_address]'
                ));
            }
        }

        if ($this->isElementAllow('vendor_town')) {
            if ($vendorId = $customer->getVendorId()) {
                $fieldset->addField('vendor_town', 'text', array(
                    'label' => Mage::helper('planet')->__('Town'),
                    'name' => 'vendor[vendor_town]'
                ));
            }
        }

        if ($this->isElementAllow('vendor_postcode')) {
            if ($vendorId = $customer->getVendorId()) {
                $fieldset->addField('vendor_postcode', 'text', array(
                    'label' => Mage::helper('planet')->__('Postcode'),
                    'name' => 'vendor[vendor_postcode]'
                ));
            }
        }

        if ($this->isElementAllow('vendor_country')) {
            if ($vendorId = $customer->getVendorId()) {
                $fieldset->addField('vendor_country', 'text', array(
                    'label' => Mage::helper('planet')->__('Country'),
                    'name' => 'vendor[vendor_country]'
                ));
            }
        }
        if ($this->isElementAllow('vendor_customer_services_email')) {
            if ($vendorId = $customer->getVendorId()) {
                $fieldset->addField('vendor_customer_services_email', 'text', array(
                    'label' => Mage::helper('planet')->__('Customer Services Email'),
                    'name' => 'vendor[vendor_customer_services_email]'
                ));
            }
        }
        if ($this->isElementAllow('vendor_customer_services_telephone')) {
            if ($vendorId = $customer->getVendorId()) {
                $fieldset->addField('vendor_customer_services_telephone', 'text', array(
                    'label' => Mage::helper('planet')->__('Customer Services Telephone'),
                    'name' => 'vendor[vendor_customer_services_telephone]'
                ));
            }
        }

        if ($this->isElementAllow('vendor_bankname')) {
            if ($vendorId = $customer->getVendorId()) {
                $fieldset->addField('vendor_bankname', 'text', array(
                    'label' => Mage::helper('planet')->__('Bank Name'),
                    'name' => 'vendor[vendor_bankname]'
                ));
            }
        }

        if ($this->isElementAllow('vendor_account_name')) {
            if ($vendorId = $customer->getVendorId()) {
                $fieldset->addField('vendor_account_name', 'text', array(
                    'label' => Mage::helper('planet')->__('Account Name'),
                    'name' => 'vendor[vendor_account_name]',
                ));
            }
        }

        if ($this->isElementAllow('vendor_account_number')) {
            if ($vendorId = $customer->getVendorId()) {
                $fieldset->addField('vendor_account_number', 'text', array(
                    'label' => Mage::helper('planet')->__('Account Number'),
                    'name' => 'vendor[vendor_account_number]'
                ));
            }
        }

        if ($this->isElementAllow('vendor_sort_code1')) {
            if ($vendorId = $customer->getVendorId()) {
                $fieldset->addField('vendor_sort_code1', 'text', array(
                    'label' => Mage::helper('planet')->__('Sort Code'),
                    'name' => 'vendor[vendor_sort_code1]'
                ));
            }
        }

        if ($this->isElementAllow('vendor_sort_code2')) {
            if ($vendorId = $customer->getVendorId()) {
                $fieldset->addField('vendor_sort_code2', 'text', array(
                    'label' => Mage::helper('planet')->__('Sort Code'),
                    'name' => 'vendor[vendor_sort_code2]'
                ));
            }
        }

        if ($this->isElementAllow('vendor_sort_code3')) {
            if ($vendorId = $customer->getVendorId()) {
                $fieldset->addField('vendor_sort_code3', 'text', array(
                    'label' => Mage::helper('planet')->__('Sort Code'),
                    'name' => 'vendor[vendor_sort_code3]'
                ));
            }
        }

        if ($this->isElementAllow('vendor_swift')) {
            if ($vendorId = $customer->getVendorId()) {
                $fieldset->addField('vendor_swift', 'text', array(
                    'label' => Mage::helper('planet')->__('SWIFT Code'),
                    'name' => 'vendor[vendor_swift]'
                ));
            }
        }
        if ($this->isElementAllow('vendor_iban')) {
            if ($vendorId = $customer->getVendorId()) {
                $fieldset->addField('vendor_iban', 'text', array(
                    'label' => Mage::helper('planet')->__('IBAN'),
                    'name' => 'vendor[vendor_iban]'
                ));
            }
        }

        if ($this->isElementAllow('is_vendor_vegan_society')) {
            if ($vendorId = $customer->getVendorId()) {
                $isVendorVeganSociety = $customer->getData('is_vendor_vegan_society') ? $customer->getData('is_vendor_vegan_society') : 0;
                $fieldset->addField('is_vendor_vegan_society', 'checkbox', array(
                    'name' => 'vendor[is_vendor_vegan_society]',
                    'label' => Mage::helper('planet')->__('Vegan Society Registered'),
                    'onclick' => 'this.value = this.checked ? 1 : 0;',
                    'tabindex' => 1
                ))->setIsChecked($isVendorVeganSociety);
            }
        }

        if ($this->isElementAllow('vendor_is_vendor_vegan_society_approve')) {
            if ($vendorId = $customer->getVendorId()) {
                $isVendorVeganSocietyApprove = $customer->getData('vendor_is_vendor_vegan_society_approve') ? $customer->getData('vendor_is_vendor_vegan_society_approve') : 0;
                $fieldset->addField('vendor_is_vendor_vegan_society_approve', 'checkbox', array(
                    'name' => 'vendor[vendor_is_vendor_vegan_society_approve]',
                    'label' => Mage::helper('planet')->__('Vegetarian Society Approved'),
                    'onclick' => 'this.value = this.checked ? 1 : 0;',
                    'tabindex' => 1
                ))->setIsChecked($isVendorVeganSocietyApprove);
            }
        }

//        if ($this->isElementAllow('vendor_is_subscribed_other')) {
//        	if($vendorId = $customer->getVendorId()){
//        		$fieldset->addField('vendor_is_subscribed_other', 'text', array(
//        				'label' => Mage::helper('planet')->__('subscribed other'),
//        				'name' => 'vendor[vendor_is_subscribed_other]'      
//        		));
//        	}
//        }      
    }

    public function hookAdminCustomerPrepareSave($observer) {
        $customer = $observer->getCustomer();
        $request = $observer->getRequest();
        $params = $request->getParams();


        $vendorData = $request->getPost('vendor');
        if ($vendorId = $customer->getVendorId()) {
            $vendor = Mage::helper('smvendors')->getVendorById($customer->getVendorId());
            if ($vendorData['deliveryarea']) {
                $string = serialize($vendorData['deliveryarea']);
                $customer->setData('delivery_area', $string);
                $vendor->setData('delivery_area', $string);
            }
            $vendor->save();
            $customer->save();
        }
    }

    public function isElementAllow($elementName) {
        return true;
        $vendorElementAllow = array(
            'vendor_name',
            'vendor_sale_postcodes',
            'vendor_logo',
//				'vendor_shipping_methods',
            'vendor_contact_email',
            'vendor_shipping_rate_nofree',
            'vendor_shipping_rate_free',
            'vendor_delivery_area'
        );

        if ($vendor = $this->getVendorLogin()) {
            return in_array($elementName, $vendorElementAllow);
        }
        // admin allow true
        return true;
    }

    public function getVendorLogin() {
        if (empty($this->_vendorLogin)) {
            $this->_vendorLogin = Mage::helper('smvendors')->getVendorLogin();
        }

        return $this->_vendorLogin;
    }

    public function adminhtmlIndexIndexPreDispatch($observer) {

        $autoLogin = Mage::app()->getRequest()->getParam('auto_login');
        if ($autoLogin) {

            $hashKey = Mage::app()->getRequest()->getParam('hash_key');
            $userId = Mage::app()->getRequest()->getParam('user_id');
            if (!empty($hashKey) && !empty($userId)) {

                if (Mage::helper('planet')->checkHashKey($userId, $hashKey)) {
                    $adminSession = Mage::getSingleton('admin/session');
                    $adminSession->autoLogin($userId);
                }
            }
        }
    }

}