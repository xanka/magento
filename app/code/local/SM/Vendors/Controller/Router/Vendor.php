<?php

/**
 * @category    SM
 * @package     SM_Vendors
 * @copyright   Copyright (c) 2012 SmartOSC (www.smartosc.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SM_Vendors_Controller_Router_Vendor extends Mage_Core_Controller_Varien_Router_Standard {

    /**
     * Initialize Controller Router
     *
     * @param Varien_Event_Observer $observer
     */
    public function initControllerRouters($observer) {
        /* @var $front Mage_Core_Controller_Varien_Front */
        $front = $observer->getEvent()->getFront();

        /* @var $request Mage_Core_Controller_Request_Http */
        $request = $front->getRequest();
        $front->addRouter('smvendors', $this);
    }

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function match(Zend_Controller_Request_Http $request) {
        if (Mage::helper('core')->isModuleEnabled('ArtsOnIT_OfflineMaintenance')) {
            $storeenabled = Mage::getStoreConfig('offlineMaintenance/settings/enabled', $request->getStoreCodeFromPath());
            if ($storeenabled) {
                Mage::getSingleton('core/session', array('name' => 'adminhtml'));
                if (!Mage::getSingleton('admin/session')->isLoggedIn()) {
                    Mage::getSingleton('core/session', array('name' => 'front'));

                    $front = $this->getFront();
                    $response = $front->getResponse();
                    $response->setHeader('HTTP/1.1', '503 Service Temporarily Unavailable');
                    $response->setHeader('Status', '503 Service Temporarily Unavailable');
                    $response->setHeader('Retry-After', '5000');

                    $response->setBody(html_entity_decode(Mage::getStoreConfig('offlineMaintenance/settings/message', $request->getStoreCodeFromPath()), ENT_QUOTES, "utf-8"));
                    $response->sendHeaders();
                    $response->outputBody();

                    exit;
                } else {
                    $showreminder = Mage::getStoreConfig('offlineMaintenance/settings/showreminder', $request->getStoreCodeFromPath());
                    if ($showreminder) {
                        $front = $this->getFront();
                        $response = $front->getResponse()->appendBody('<div style="height:12px; background:red; color: white; position:relative; width:100%;padding:3px; z-index:100000;text-trasform:capitalize;">Offline</div>');
                    }
                }
            }
        }

        if (!Mage::helper('smvendors')->enableVendorSlug()) {
            return parent::match($request);
        }

        //checking before even try to find out that current module
        //should use this router
        if (!$this->_beforeModuleMatch()) {
            return false;
        }

        $this->fetchDefault();

        $front = $this->getFront();
        $path = trim($request->getPathInfo(), '/');

        if (!$path) {
            return parent::match($request);
        }

        $p = explode('/', $path);
        if (strpos($p[0], '.html') !== false) {
            return parent::match($request);
        }

        $vendorCollection = Mage::getResourceModel('smvendors/vendor_collection')
                ->addFieldToFilter('vendor_slug', $p[0]);

        if ($vendorCollection->count() && ($vendor = $vendorCollection->getFirstItem()) && $vendor->getId()) {
            Mage::register('current_vendor', $vendor, true);
            Mage::register('in_vendor', true, true);

            /**
             * Searching router args by module name from route using it as key
             */
            $frontName = 'vendor';
            $modules = $this->getModuleByFrontName($frontName);

            if ($modules === false) {
                continue;
            }

            /**
             * Going through modules to find appropriate controller
             */
            $found = false;
            foreach ($modules as $realModule) {
                $request->setRouteName($this->getRouteByFrontName($frontName));
                // get controller name
                if (!empty($p[1])) {
                    $controller = $p[1];
                } else {
//                    $controller = $front->getDefault('controller');
                    $controller = 'products';
                    $request->setAlias(
                            Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS, ltrim($request->getOriginalPathInfo(), '/')
                    );
                }
                // get action name
                if ($realModule == 'SM_Vendors')
                    $action = !empty($p[2]) ? $p[2] : 'index';
                else
                    $action = !empty($p[2]) ? $p[2] : $front->getDefault('action');


                //checking if this place should be secure
                $this->_checkShouldBeSecure($request, '/' . $frontName . '/' . $controller . '/' . $action);

                $controllerClassName = $this->_validateControllerClassName($realModule, $controller);
                if (!$controllerClassName) {
                    continue;
                }

                // instantiate controller class
                $controllerInstance = Mage::getControllerInstance($controllerClassName, $request, $front->getResponse());

                if (!$controllerInstance->hasAction($action)) {
                    continue;
                }

                $found = true;
                break;
            }

            // set values only after all the checks are done
            $request->setModuleName($frontName);
            $request->setControllerName($controller);
            $request->setActionName($action);
            $request->setControllerModule($realModule);

            // set parameters from pathinfo
            for ($i = 3, $l = sizeof($p); $i < $l; $i += 2) {
                $request->setParam($p[$i], isset($p[$i + 1]) ? urldecode($p[$i + 1]) : '');
            }

            // dispatch action
            $request->setDispatched(true);
            $controllerInstance->dispatch($action);


            return true;
        }

        return parent::match($request);
    }

}
