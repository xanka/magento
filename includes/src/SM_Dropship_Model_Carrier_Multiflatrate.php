<?php

class SM_Dropship_Model_Carrier_Multiflatrate
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'vendormultiflatrate';
    protected $_isFixed = true;


    /**
     * Enter description here...
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $vendorId = $request->getVendorId();
    	if (!$this->getConfigFlag('active')) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');
        
        $freeMethod = $this->getFreeShippingMethodByVendor($vendorId);
        $isFreeShip = false;
        if(!empty($freeMethod)){
        	$orderAmount = $this->getOrderAmount($request);
        	if($orderAmount >= $freeMethod->getOrderAmountLimit()){
	        	$method = Mage::getModel('shipping/rate_result_method');
	            $method->setCarrier($this->_code);
	            $method->setCarrierTitle($this->getConfigData('title'));
	
	            $method->setMethod('rate'.$freeMethod->getId());
	            $method->setMethodTitle($freeMethod->getTitle());
	            $method->setMethodDescription($freeMethod->getDescription());
	
	            $shippingPrice = 0;
	            $method->setPrice($shippingPrice);
	            $method->setCost($shippingPrice);
	            $result->append($method);
	            $isFreeShip = true;
        	}
        }
        
        if(!$isFreeShip){
	        $rates = Mage::getResourceModel('smdropship/shipping_multiflatrate_collection')->addNoFreeToFilter();
	        foreach($rates as $rate){
		        if ($rate->getActive() && in_array($vendorId, $rate->getVendorIds())) {
		            $method = Mage::getModel('shipping/rate_result_method');
		
		            $method->setCarrier($this->_code);
		            $method->setCarrierTitle($this->getConfigData('title'));
		
		            $method->setMethod('rate'.$rate->getId());
		            $method->setMethodTitle($rate->getTitle());
		            $method->setMethodDescription($rate->getDescription());
		            $shippingPrice = $rate->getPrice();

		            $method->setPrice($shippingPrice);
		            $method->setCost($shippingPrice);
		            $result->append($method);
		        }
	        }
        }
        return $result;
    }

    protected function getFreeShippingMethodByVendor($vendorId){
    	$rates = Mage::getResourceModel('smdropship/shipping_multiflatrate_collection')->addFreeToFilter();
    	$result = false;
    	foreach($rates as $rate){
    		if ($rate->getActive() && in_array($vendorId, $rate->getVendorIds())) {
    			$result = $rate;
    			break;
    		}
    	}
    	return $result;
    }
    
    public function getAllowedMethods()
    {
        return array('vendormultiflatrate'=>$this->getConfigData('name'));
    }

    public function getOrderAmount($request){
    	$orderAmount = 0;
    	if ($request->getAllItems()) {
    		foreach($request->getAllItems() as $item)
    		{
    			$orderAmount += floatval($item->getRowTotal());
    		}
    	}
    	return $orderAmount;
    }
}
