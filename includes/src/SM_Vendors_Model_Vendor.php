<?php

class SM_Vendors_Model_Vendor extends Mage_Core_Model_Abstract {

    protected $_productCollection;
    protected $_userModel = null;
    protected $_customerModel = null;

    public function _construct() {
        parent::_construct();
        $this->_init('smvendors/vendor');
    }

    /**
     * (non-PHPdoc)
     * @see Mage_Core_Model_Abstract::_afterLoad()
     */
    public function _afterLoad() {


        return parent::_afterLoad();
    }

    public function _afterSave() {
        if (!$this->getVendorPrefix()) {
            $this->getResource()->_saveVendorPrefix($this);
        }
        if (sizeof($this->getStaticPages()) == 0) {
            $this->createDefaultPage();
        }
        return parent::_afterSave();
    }

    protected function _afterDelete() {
        if ($this->getUserModel()->getId()) {
            $this->getUserModel()->delete();
        }
        return parent::_afterDelete();
    }

    /**
     * @return Mage_Admin_Model_User
     */
    public function getUserModel() {
        if ($this->_userModel === null) {
            $this->_userModel = Mage::getModel('admin/user');
            if ($this->getUserId()) {
                $this->_userModel->load($this->getUserId());
            }
        }
        return $this->_userModel;
    }

    /**
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomerModel() {
        if ($this->_customerModel === null) {
            $this->_customerModel = Mage::getModel('customer/customer');
            if ($this->getCustomerId()) {
                $this->_customerModel->load($this->getCustomerId());
            }
        }
        return $this->_customerModel;
    }

    public function getStaticPages() {
        $collection = Mage::getResourceModel('smvendors/page_collection')->addVendorToFilter($this->getId());
        return $collection;
    }

    public function loadByUserId($id) {
        $vendor = $this->getResource()->getByUserId($id);
        if (!empty($vendor)) {

            $this->setData($vendor);
        }
        return $this;
    }

    public function loadByCustomerId($id) {
        $vendor = $this->getResource()->getByCustomerId($id);
        if (!empty($vendor)) {

            $this->setData($vendor);
        }
        return $this;
    }

    public function getRoleId() {
        $roleId = 1;
        return $roleId;
    }

    public function getAvaiableShippingMethods() {
        $avaiableShippingMethods = array();
        if ($vendorShippingMethod = $this->getData('vendor_shipping_methods')) {
            $avaiableShippingMethods = explode(',', $vendorShippingMethod);
        }
        return $avaiableShippingMethods;
    }

    /**
     * Get vendor logo URL
     * 
     * @return string
     */
    public function getVendorLogoUrl() {
        $vendorLogo = $this->getVendorLogo();
        return $vendorLogo ? Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $vendorLogo : '';
    }

    /**
     * 
     */
    public function getVendorUrl() {
        if (($vendorSlug = $this->getVendorSlug())) {
            return Mage::getUrl($vendorSlug);
        }

        return Mage::getUrl('vendor/view/index', array('id' => $this->getId()));
    }

    /**
     * Get vendor products
     * 
     * @return collection
     */
    public function getProductCollection() {
        if (empty($this->_productCollection)) {
            $collection = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('sm_product_vendor_id', $this->getId());
            $this->_productCollection = $collection;
        }
        return $this->_productCollection;
    }

    public function getEmail() {
        if ($this->getVendorContactEmail()) {
            return $this->getVendorContactEmail();
        } else {
            return $this->getUserModel()->getEmail();
        }
    }

    public function createDefaultPage() {
        $pages = array(
            'terms-and-conditions' => array(
                'title' => 'Our Company',
                'identifier' => 'terms-and-conditions',
                'content' => '',
                'root_template' => 'two_columns_left'
            ),
            'return-policy' => array(
                'title' => 'Delivery',
                'identifier' => 'return-policy',
                'content' => '',
                'root_template' => 'two_columns_left'
            ),
            'imprint' => array(
                'title' => 'Returns',
                'identifier' => 'imprint',
                'content' => '',
                'root_template' => 'two_columns_left'
            ),
            'contact' => array(
                'title' => 'Contact us',
                'identifier' => 'contact',
                'content' => '',
                'root_template' => 'two_columns_left'
            )          
        );

        foreach ($pages as $page) {
            $model = Mage::getModel('smvendors/page');
            $model->setData($page);
            $model->setVendorId($this->getId());
            $model->save();
        }
    }

}