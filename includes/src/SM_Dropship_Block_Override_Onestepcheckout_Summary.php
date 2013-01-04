<?php

class SM_Dropship_Block_Override_Onestepcheckout_Summary extends Mage_Checkout_Block_Onepage_Abstract {

    /**
     * 
     * @return Array
     */
    public function getVendorTotals($vendorId) {
        if (empty($this->_vendorTotals[$vendorId])) {
            $vendorItems = $this->getVendorItems();
            if (!empty($vendorItems[$vendorId])) {
                $vendorItems = $vendorItems[$vendorId];

                if (empty($this->_vendorTotals[$vendorId])) {
                    $this->_vendorTotals[$vendorId] = array(
                        'qty' => 0,
                        'subtotal' => 0,
                        'discount' => 0
                    );
                }
                foreach ($vendorItems as $item) {
                    $this->_vendorTotals[$vendorId]['subtotal'] += $item->getRowTotal();
                    $this->_vendorTotals[$vendorId]['tax'] += $item->getTaxAmount();
                }

                $this->_vendorTotals[$vendorId]['shipping_price'] = $this->getShippingPriceByVendor($vendorId);
                $this->_vendorTotals[$vendorId]['subtotal'] += $this->_vendorTotals[$vendorId]['shipping_price'];
                $this->_vendorTotals[$vendorId]['subtotal'] += $this->_vendorTotals[$vendorId]['tax'];
            }
        }
        return $this->_vendorTotals[$vendorId];
    }

    /**
     * 
     * @return Array
     */
    public function getShippingPriceByVendor($vendorId) {
        $vendorItems = $this->getVendorItems();
        $result = array();
        $result['shipping_methods_by_vendor'] = array();
        $selectedShippingMethod = Mage::getSingleton('checkout/cart')->getQuote()->getShippingAddress()->getShippingMethod();
        $selectedShippingMethod = str_replace('dropshipping_', '', $selectedShippingMethod);
        $selectedShippingMethods = explode('|', $selectedShippingMethod);

        $rates = $this->getDropShipRatesByVendor($vendorId);
        $shippingPrice = 0;
        foreach ($rates as $rate) {
            if (in_array($rate->getCode(), $selectedShippingMethods)) {
                $shippingPrice = $rate->getPrice();
                break;
            }
        }
        return $shippingPrice;
    }

    public function getDropShipRatesByVendor($vendorId) {
        $_shippingRateGroups = $this->getEstimateRates();

        if (!empty($_shippingRateGroups['dropshipping'])) {
            $dropShippingRates = $_shippingRateGroups['dropshipping'];
            $firstRate = reset($dropShippingRates);
            $shippingRatesByVendor = $firstRate->getShippingRatesByVendors();

            return $shippingRatesByVendor[$vendorId]['shipping_methods'];
        }

        return false;
    }

    /**
     * 
     * @return Array
     */
    public function getEstimateRates() {
        $shippingMethodDetail = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getShippingMethodDetail();

        if (empty($this->_rates)) {
            $groups = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getGroupedAllShippingRates();
            $this->_rates = $groups;
        }

        return $this->_rates;
    }

}
