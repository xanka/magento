<?php

class SM_Dropship_Helper_Data extends Mage_Core_Helper_Abstract {

    public function dropShipIsActive() {
        return Mage::getStoreConfig('carriers/dropshipping/active');
    }

    public function getAllShippingMethodToOptionArray() {
        $allShippingMethod = Mage::getModel('shipping/config')->getActiveCarriers();
        $shippingMethods = array();
        foreach ($allShippingMethod as $shippingMethod) {
            if ($shippingMethod->getId() != 'dropshipping') {
                $shippingMethods[] = array(
                    'label' => $shippingMethod->getId(),
                    'value' => $shippingMethod->getId()
                );
            }
        }
        return $shippingMethods;
    }

    public function getAllShippingRateFreeToOptionArray(){
    	return Mage::getResourceModel('smdropship/shipping_multiflatrate_collection')->addFreeToFilter()->toOptionArray();
    }
    
    public function getAllShippingRateNoFreeToOptionArray(){
    	return Mage::getResourceModel('smdropship/shipping_multiflatrate_collection')->addNoFreeToFilter()->toOptionArray();
    }
}