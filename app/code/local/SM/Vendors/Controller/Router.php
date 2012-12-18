<?php

/**
 * @category    SM
 * @package     SM_Vendors
 * @copyright   Copyright (c) 2012 SmartOSC (www.smartosc.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SM_Vendors_Controller_Router extends Mage_Core_Controller_Varien_Router_Standard {

    /**
     * Initialize Controller Router
     *
     * @param Varien_Event_Observer $observer
     */
    public function handleVendorDomain($observer) {
        /* @var $front Mage_Core_Controller_Varien_Front */
        $front = $observer->getEvent()->getFront();

        /* @var $request Mage_Core_Controller_Request_Http */
        $request = $front->getRequest();
        $domainParts = explode('.', $_SERVER['HTTP_HOST']);

        if (sizeof($domainParts) > 0 && preg_match('/^[a-z\d]+$/', $domainParts[0])) {
            $vendorCollection = Mage::getResourceModel('smvendors/vendor_collection')
                    ->addFieldToFilter('vendor_domain', $domainParts[0]);

            if ($vendorCollection->count() && ($vendor = $vendorCollection->getFirstItem()) && $vendor->getId()) {
                $baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, Mage::app()->getStore()->isCurrentlySecure());
                $uri = @parse_url($baseUrl);
                $host = isset($uri['host']) ? $uri['host'] : '';

                $fakeHost = str_replace($domainParts[0], 'www', $_SERVER['HTTP_HOST']);

                if ($host == $fakeHost) {
                    Mage::register('prerouting_current_vendor', $vendor, true);
                    Mage::register('prerouting_in_vendor', true, true);

                    $_SERVER['ORIGINAL_HTTP_HOST'] = $_SERVER['HTTP_HOST'];
                    $_SERVER['HTTP_HOST'] = $fakeHost;
                    $_SERVER['SERVER_NAME'] = $fakeHost;
                }
            }
        }
    }

}
