<?php

class SM_Vendors_Block_Adminhtml_Vendor_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected $_vendorLogin;

    protected function _prepareForm() {
    	$customer = Mage::registry('current_customer');
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('vendor_form', array('legend' => Mage::helper('smvendors')->__('Merchant information')));
        $version = substr(Mage::getVersion(), 0, 3);
        //$config = (($version == '1.4' || $version == '1.5') ? "'config' => Mage::getSingleton('banner/wysiwyg_config')->getConfig()" : "'class'=>''");

        if ($this->isElementAllow('vendor_name')) {
            $fieldset->addField('vendor_name', 'text', array(
                'label' => Mage::helper('smvendors')->__('Company Name'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'vendor[vendor_name]',
            ));
        }

        if ($this->isElementAllow('vendor_slug')) {
            $fieldset->addField('vendor_slug', 'text', array(
                'label' => Mage::helper('smvendors')->__('URL Slug'),
                'class' => 'validate-vendor-slug',
                'unique' => true,
                'required' => true,
                'name' => 'vendor[vendor_slug]',
            ));
        }

        if ($this->isElementAllow('vendor_domain')) {
            $fieldset->addField('vendor_domain', 'hidden', array(
                'label' => Mage::helper('smvendors')->__('Domain'),
                'class' => 'validate-vendor-domain',
                'unique' => true,
                'name' => 'vendor[vendor_domain]',
            ));
        }

        if ($this->isElementAllow('vendor_commission')) {
            $fieldset->addField('vendor_commission', 'text', array(
                'label' => Mage::helper('smvendors')->__('Commission'),
                'name' => 'vendor[vendor_commission]',
            ));
        }

        if ($this->isElementAllow('vendor_contact_email')) {
            $fieldset->addField('vendor_contact_email', 'text', array(
                'label' => Mage::helper('smvendors')->__('Contact Email'),
                'class' => 'validate-email',
                'name' => 'vendor[vendor_contact_email]',
            ));
        }

        if ($this->isElementAllow('vendor_sale_postcodes')) {
            $fieldset->addField('vendor_sale_postcodes', 'hidden', array(
                'label' => Mage::helper('smvendors')->__('Sales postcodes'),
                'name' => 'vendor[vendor_sale_postcodes]',
            ));
        }

        if ($this->isElementAllow('vendor_logo')) {
            $fieldset->addField('vendor_logo', 'image', array(
                'label' => Mage::helper('smvendors')->__('Logo'),
                'name' => 'vendor_logo',
                'note' => Mage::helper('smvendors')->__('The image you upload should be 121x141 px'),
            ));
        }

        if ($this->isElementAllow('vendor_feature')) {
            $fieldset->addField('vendor_feature', 'select', array(
                'label' => Mage::helper('smvendors')->__('Feature'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'vendor[vendor_feature]',
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => Mage::helper('smvendors')->__('Yes'),
                    ),
                    array(
                        'value' => 0,
                        'label' => Mage::helper('smvendors')->__('No'),
                    ),
                ),
            ));
        }

        if ($this->isElementAllow('vendor_status')) {
            $fieldset->addField('vendor_status', 'select', array(
                'label' => Mage::helper('smvendors')->__('Status'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'vendor[vendor_status]',
                'values' => array(
                    array(
                        'value' => 1,
                        'label' => Mage::helper('smvendors')->__('Active'),
                    ),
                    array(
                        'value' => 0,
                        'label' => Mage::helper('smvendors')->__('Inactive'),
                    ),
                ),
            ));
        }

        if ($this->isElementAllow('description')) {
            $fieldset->addField('description', 'textarea', array(
                'label' => Mage::helper('smvendors')->__('Company Description'),
                'name' => 'vendor[description]',
                'note' => 'this will be seen on the website, sell yourself!',
            ));
        }

        // dispatch events for further column adding
        Mage::dispatchEvent('smvendors_adminhtml_vendor_edit_tab_prepare_form', array('fieldset' => $fieldset));


        

        if ($customer->getId()) {
            $form->addField('entity_id', 'hidden', array(
                'name' => 'customer_id',
            ));
            $form->setValues($customer->getData());
        } else {
//			$form->setValues(array('vendor_shipping_methods'=>'flatrate'));
        }

        //$form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function isElementAllow($elementName) {
        $vendorElementAllow = array(
            'vendor_name',
            'vendor_sale_postcodes',
            'vendor_logo',
//				'vendor_shipping_methods',
            'vendor_contact_email',
            'description'
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

}