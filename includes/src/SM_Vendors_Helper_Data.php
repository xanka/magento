<?php

class SM_Vendors_Helper_Data extends Mage_Core_Helper_Abstract {

    const XML_PATH_SHOW_NEW_ARRIVAL = 'smvendors/frontpage/show_new_arrival';
    const XML_PATH_ENABLE_VENDOR_SLUG = 'smvendors/general/enable_vendor_slug';
    const XML_PATH_USE_CUSTOM_PDF = 'smvendors/general/use_custom_pdf';
    const XML_PATH_SIMPLE_PRODUCT_CREATE = 'smvendors/general/simple_product_create';

    protected static $egridImgDir = null;
    protected static $egridImgURL = null;
    protected static $egridImgThumb = null;
    protected static $egridImgThumbWidth = null;
    protected $_allowedExtensions = Array();
    protected $_loggedVendor = null;
    protected $_vendorNames = null;
    protected $_productToVendor = array();
    protected $_currentVendor = null;
    protected $_enableVendorSlug = null;

    public function __construct() {
        self::$egridImgDir = Mage::getBaseDir('media') . DS;
        self::$egridImgURL = Mage::getBaseUrl('media');
        self::$egridImgThumb = "thumb/";
        self::$egridImgThumbWidth = 100;
    }

    public function getVendorRole() {
        return Mage::getStoreConfig('smvendors/roles/vendor');
    }

    public function getVendorLogin() {
        if ($this->_loggedVendor === null) {
            $this->_loggedVendor = false;

            $user = Mage::getSingleton('admin/session')->getUser();
            if (!$user) {
                $this->_loggedVendor = false;
            } else {
                $vendor = Mage::getModel('smvendors/vendor')->loadByUserId($user->getUserId());
                if ($vendor->getId()) {
                    $this->_loggedVendor = $vendor;
                } else {
                    $vendor = Mage::getModel('smvendors/vendor')->load($user->getVendorId());
                    if ($vendor->getId()) {
                        $this->_loggedVendor = $vendor;
                    }
                }
            }

            //If admin is logged in, check if this operation must be done as a vendor.
            if (!$this->_loggedVendor) {
                $doAsVendor = Mage::app()->getRequest()->getParam('do_as_vendor');
                if ($doAsVendor) {
                    $vendor = Mage::getModel('smvendors/vendor')->load($doAsVendor);
                    if ($vendor->getId()) {
                        $this->_loggedVendor = $vendor;
                    }
                }
            }
        }

        return $this->_loggedVendor;
    }

    public function addDoAsVendorToParams(&$params) {
        if ($vendorId = Mage::app()->getRequest()->getParam('do_as_vendor')) {
            $params['do_as_vendor'] = $vendorId;
        }
    }

    public function updateDirSepereator($path) {
        return str_replace('\\', DS, $path);
    }

    public function getImageUrl($image_file) {
        $url = false;
        if (file_exists(self::$egridImgDir . self::$egridImgThumb . $this->updateDirSepereator($image_file)))
            $url = self::$egridImgURL . self::$egridImgThumb . $image_file;
        else
            $url = self::$egridImgURL . $image_file;
        return $url;
    }

    public function getFileExists($image_file) {
        $file_exists = false;
        $file_exists = file_exists(self::$egridImgDir . $this->updateDirSepereator($image_file));
        return $file_exists;
    }

    public function getImageThumbSize($image_file) {
        $img_file = $this->updateDirSepereator(self::$egridImgDir . $image_file);
        if ($image_file == '' || !file_exists($img_file))
            return false;
        list($width, $height, $type, $attr) = getimagesize($img_file);
        $a_height = (int) ((self::$egridImgThumbWidth / $width) * $height);
        return Array('width' => self::$egridImgThumbWidth, 'height' => $a_height);
    }

    /**
     * 
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getProductDisplayName($product) {
        $vendorId = $this->getProductVendorId($product);
        $vendorName = $this->getVendorName($vendorId);

        return ($vendorName ? '' . $vendorName . ' - ' : '') . $this->escapeHtml($product->getName());
    }

    /**
     * Retrieve current vendor
     *
     * @return SM_Vendors_Model_Vendor
     */
    public function getVendor() {
        if (empty($this->_vendor)) {
            $this->_vendor = Mage::registry('current_vendor');
        }

        return $this->_vendor;
    }

    /**
     * return vendor register link
     *
     * @return string
     */
    public function getVendorRegisterUrl() {
        return $this->_getUrl('vendor/account/create');
    }

    /**
     *
     * @param Mage_Catalog_Model_Product $product
     * @return SM_Vendors_Model_Vendor
     */
    public function getVendorByProduct($product) {
        $productId = $product->getId();

        if (!isset($this->_productToVendor[$productId])) {
            $vendorId = (int) $product->getSmProductVendorId();
            $vendor = Mage::getModel('smvendors/vendor')->load($vendorId);

            $this->_productToVendor[$productId] = $vendor;
        }

        return $this->_productToVendor[$productId];
    }

    public function getVendorById($id) {
        $vendor = Mage::getModel('smvendors/vendor')->load($id);
        return $vendor;
    }

    /**
     *
     * @param Mage_Catalog_Model_Product $product
     * @return number
     */
    public function getProductVendorId($product) {
        return (int) $product->getSmProductVendorId();
    }

    /**
     *
     * @param SM_Vendors_Model_Vendor $vendor
     * @return string
     */
    public function getVendorUrl($vendor) {
        if ($this->enableVendorSlug() && ($vendorSlug = $vendor->getVendorSlug())) {
            return $this->_getUrl($vendorSlug);
        }

        return $this->_getUrl('vendor/index/profile', array('id' => $vendor->getId()));
    }

    /**
     *
     * @param SM_Vendors_Model_Vendor $vendor
     * @return string
     */
    public function getVendorProfileUrl($vendor) {
        if ($this->enableVendorSlug() && ($vendorSlug = $vendor->getVendorSlug())) {
            return $this->_getUrl($vendorSlug);
        }

        return $this->_getUrl('vendor/index/profile', array('id' => $vendor->getId()));
    }

    public function getVendorListUrl() {
        return $this->_getUrl('vendor/list');
    }

    /**
     *
     * @param SM_Vendors_Model_Vendor $vendor
     * @return string
     */
    public function getVendorImage($vendor) {
        return $this->getImageUrl($vendor->getVendorLogo());
    }

    /**
     *
     * @param
     * @return string
     */
    public function getCurrentCustomerPostcode() {
        $customer = Mage::helper('customer')->getCustomer();
        $billingAddress = $customer->getDefaultBillingAddress();
        if (!empty($billingAddress)) {
            if ($billingAddress->getPostcode()) {
                $postcode = $billingAddress->getPostcode();
                return $postcode;
            }
        }
        return '';
    }

    public function getVendorName($vendorId) {
        if ($this->_vendorNames === null) {
            $this->_vendorNames = array();
            $collection = Mage::getResourceModel('smvendors/vendor_collection')
                    ->addFieldToSelect(array('vendor_id', 'vendor_name'))
                    ->load();
            foreach ($collection as $item) {
                $this->_vendorNames[$item->getVendorId()] = $item->getVendorName();
            }
        }

        return isset($this->_vendorNames[$vendorId]) ? $this->_vendorNames[$vendorId] : '';
    }

    /**
     *
     * @return boolean
     */
    public function showNewArrival() {
        return Mage::getStoreConfig(self::XML_PATH_SHOW_NEW_ARRIVAL);
    }

    public function getPrefixFromId($id) {
        $prefix = base_convert((int) $id, 10, 32);
        $prefix = str_pad($prefix, 5, '0', STR_PAD_LEFT);
        $prefix = strtoupper($prefix);
        return $prefix;
    }

    /**
     * @return boolean
     */
    public function enableVendorSlug() {
        if (is_null($this->_enableVendorSlug)) {
            $this->_enableVendorSlug = Mage::getStoreConfig(self::XML_PATH_ENABLE_VENDOR_SLUG);
        }

        return $this->_enableVendorSlug;
    }

    /**
     * 
     * @return 
     */
    public function getCurrentVendor() {
        if (is_null($this->_currentVendor)) {
            $this->_currentVendor = Mage::registry('current_vendor');
        }

        return $this->_currentVendor;
    }

    public function getLayoutOutput($params) {
        $skipParams = array('handle', 'area');

        $layout = Mage::getModel('core/layout');
        /* @var $layout Mage_Core_Model_Layout */
        if (isset($params['area'])) {
            $layout->setArea($params['area']);
        } else {
            $layout->setArea(Mage::app()->getLayout()->getArea());
        }

        $layout->getUpdate()->addHandle($params['handle']);
        $layout->getUpdate()->load();

        $layout->generateXml();
        $layout->generateBlocks();

        foreach ($layout->getAllBlocks() as $blockName => $block) {
            /* @var $block Mage_Core_Block_Abstract */
            foreach ($params as $k => $v) {
                if (in_array($k, $skipParams)) {
                    continue;
                }

                $block->setDataUsingMethod($k, $v);
            }
        }

        /**
         * Add output method for first block
         */
        $allBlocks = $layout->getAllBlocks();
        $firstBlock = reset($allBlocks);
        if ($firstBlock) {
            $layout->addOutputBlock($firstBlock->getNameInLayout());
        }

        $layout->setDirectOutput(false);
        return $layout->getOutput();
    }

    public function useCustomPdf() {
        return Mage::getStoreConfig(self::XML_PATH_USE_CUSTOM_PDF);
    }

    public function useSimpleProductCreate() {
        return Mage::getStoreConfig(self::XML_PATH_SIMPLE_PRODUCT_CREATE);
    }

    public function getDefaultStoreId() {
        return 1;
    }

    public function getVendorContactUrl($vendor) {
        $home_url = Mage::helper('core/url')->getHomeUrl();
        return $home_url . 'planet/contact/write/vendor/' . $vendor->getData('vendor_id');
    }

    public function getVendorResizedLogo($imageUrl, $width, $height, $section, $page) {
        if ($imageUrl == null || $imageUrl == '') {
            return null;
        }
        $imageName = substr(strrchr($imageUrl, "/"), 1);
        $imageResized = Mage::getBaseDir('media') . DS . $section . DS . "banners" . DS . 'resized_' . $page . DS . $imageName;
        $dirImg = Mage::getBaseDir() . str_replace("/", DS, strstr($imageUrl, '/media'));
        if (!file_exists($imageResized) && file_exists($dirImg)) {
            $imageObj = new Varien_Image($dirImg);
            $imageObj->constrainOnly(TRUE);
            $imageObj->keepAspectRatio(TRUE);
            $imageObj->keepFrame(FALSE);
            $imageObj->resize($width, $height);
            $imageObj->save($imageResized);
        }
        return Mage::getBaseUrl('media') . '/' . $section . "/banners/resized_" . $page . "/" . $imageName;
    }

}