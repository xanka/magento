<?php

class SM_Vendors_Model_Price_Observer {

    protected $_rulePrices = array();

    public function addFinalPriceForCustomerGroup($observer) {
        $collection = $observer->getEvent()->getCollection();
        foreach ($collection as $product) {
            if ($product instanceof Mage_Catalog_Model_Product) {
                $product->getPriceModel()->getFinalPrice(null, $product);
            }
        }
    }

    /**
     * Apply catalog price rules to product on frontend
     *
     * @see Mage_CatalogRule_Model_Observer::processFrontFinalPrice()
     * @return  Mage_CatalogRule_Model_Observer
     */
    public function processFrontFinalPrice($observer) {
        $product = $observer->getEvent()->getProduct();
        $qty = $observer->getEvent()->getQty();
        $pId = $product->getId();
        $storeId = $product->getStoreId();

        if ($observer->hasDate()) {
            $date = $observer->getEvent()->getDate();
        } else {
            $date = Mage::app()->getLocale()->storeTimeStamp($storeId);
        }

        if ($observer->hasWebsiteId()) {
            $wId = $observer->getEvent()->getWebsiteId();
        } else {
            $wId = Mage::app()->getStore($storeId)->getWebsiteId();
        }

        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($vendorSpecifiedGroups = $customer->getData('vendor_customer_group')) {
            $vendorSpecifiedGroups = explode(',', $vendorSpecifiedGroups);
            foreach ($vendorSpecifiedGroups as $group) {
                $key = "$date|$wId|$group|$pId";
                if (!isset($this->_rulePrices[$key])) {
                    $rulePrice = Mage::getResourceModel('catalogrule/rule')
                            ->getRulePrice($date, $wId, $group, $pId);
                    $this->_rulePrices[$key] = $rulePrice;
                }
                if ($this->_rulePrices[$key] !== false) {
                    $finalPrice = min($product->getData('final_price'), $this->_rulePrices[$key]);
                    $product->setFinalPrice($finalPrice);
                }
            }

            // apply group price
            foreach ($vendorSpecifiedGroups as $group) {
                $groupPrice = $this->_applyGroupPriceForVendor($product, $group);                
                if ($groupPrice > 0) {
                    $finalPrice = $product->getFinalPrice();
                    $finalPrice = min($groupPrice, $finalPrice);
                    $product->setFinalPrice($finalPrice);
                }
            }

            if ($qty) {
                $this->_applyTierPriceForVendor($product, $vendorSpecifiedGroups, $qty);
            }
        }

        return $this;
    }

    /**
     * Apply catalog price rules to product in admin
     *
     * @see Mage_CatalogRule_Model_Observer::processAdminFinalPrice()
     * @return  Mage_CatalogRule_Model_Observer
     */
    public function processAdminFinalPrice($observer) {
        $product = $observer->getEvent()->getProduct();
        $qty = $observer->getEvent()->getQty();
        $storeId = $product->getStoreId();
        $date = Mage::app()->getLocale()->storeDate($storeId);
        $key = false;
        $pId = $product->getId();

        if ($ruleData = Mage::registry('rule_data')) {
            $wId = $ruleData->getWebsiteId();
        } elseif ($product->getWebsiteId() != null && $product->getCustomerGroupId() != null) {
            $wId = $product->getWebsiteId();
        }

        $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
        /* @var $quote Mage_Adminhtml_Model_Session_Quote */
        if ($quote && ($customer = $quote->getCustomer())) {
            if ($vendorSpecifiedGroups = $customer->getData('vendor_customer_group')) {
                $vendorSpecifiedGroups = explode(',', $vendorSpecifiedGroups);
                foreach ($vendorSpecifiedGroups as $group) {
                    $key = "$date|$wId|$group|$pId";
                    if (!isset($this->_rulePrices[$key])) {
                        $rulePrice = Mage::getResourceModel('catalogrule/rule')
                                ->getRulePrice($date, $wId, $group, $pId);
                        $this->_rulePrices[$key] = $rulePrice;
                    }
                    if ($this->_rulePrices[$key] !== false) {
                        $finalPrice = min($product->getData('final_price'), $this->_rulePrices[$key]);
                        $product->setFinalPrice($finalPrice);
                    }
                }
                // apply group price
                foreach ($vendorSpecifiedGroups as $group) {
                    $groupPrice = $this->_applyGroupPriceForVendor($product, $group);                
                    if ($groupPrice > 0) {
                        $finalPrice = $product->getFinalPrice();
                        $finalPrice = min($groupPrice, $finalPrice);
                        $product->setFinalPrice($finalPrice);
                    }
                }
                if ($qty) {
                    $this->_applyTierPriceForVendor($product, $vendorSpecifiedGroups, $qty);
                }
            }
        }

        return $this;
    }

    protected function _applyGroupPriceForVendor($product, $group) {
        $groupPrices = $product->getData('group_price');

        if (is_null($groupPrices)) {
            $attribute = $product->getResource()->getAttribute('group_price');
            if ($attribute) {
                $attribute->getBackend()->afterLoad($product);
                $groupPrices = $product->getData('group_price');
            }
        }

        if (is_null($groupPrices) || !is_array($groupPrices) || count($groupPrices) == 0) {
            return $product->getPrice();
        }

        $customerGroup = $group; //$this->_getCustomerGroupId($product);

        $matchedPrice = $product->getPrice();
        foreach ($groupPrices as $groupPrice) {
            if ($groupPrice['cust_group'] == $customerGroup && $groupPrice['website_price'] < $matchedPrice) {
                $matchedPrice = $groupPrice['website_price'];
                break;
            }
        }
        return $matchedPrice;
    }

    /**
     * @see Mage_Catalog_Model_Product_Type_Price::getTierPrice()
     */
    protected function _applyTierPriceForVendor($product, $vendorSpecifiedGroups, $qty) {
        $allGroups = Mage_Customer_Model_Group::CUST_GROUP_ALL;
        $prices = $product->getData('tier_price');

        if (is_null($prices)) {
            $attribute = $product->getResource()->getAttribute('tier_price');
            if ($attribute) {
                $attribute->getBackend()->afterLoad($product);
                $prices = $product->getData('tier_price');
            }
        }

        if (is_null($prices) || !is_array($prices)) {
            return;
        }

        if ($qty) {
            $prevQty = 1;
            $prevPrice = $product->getFinalPrice();
            $prevGroup = $allGroups;

            foreach ($prices as $price) {
                if (!in_array($price['cust_group'], $vendorSpecifiedGroups) && $price['cust_group'] != $allGroups) {
                    // tier not for current customer group nor is for all groups
                    continue;
                }
                if ($qty < $price['price_qty']) {
                    // tier is higher than product qty
                    continue;
                }
                if ($price['price_qty'] < $prevQty) {
                    // higher tier qty already found
                    continue;
                }
                if ($price['price_qty'] == $prevQty && $prevGroup != $allGroups && $price['cust_group'] == $allGroups) {
                    // found tier qty is same as current tier qty but current tier group is ALL_GROUPS
                    continue;
                }
                if ($price['website_price'] < $prevPrice) {
                    $prevPrice = $price['website_price'];
                    $prevQty = $price['price_qty'];
                    $prevGroup = $price['cust_group'];
                }
            }

            $product->setFinalPrice($prevPrice);
        }
    }

}