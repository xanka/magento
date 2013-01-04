<?php

class SM_Dropship_Model_Override_Checkout_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{
    public function saveShippingMethod($shippingMethod)
    {
		$result = parent::saveShippingMethod($shippingMethod);
		
		if(Mage::helper('smdropship')->dropShipIsActive()){
			$rate = $this->getQuote()->getShippingAddress()->getShippingRateByCode($shippingMethod);
			if($rate->getCarrier() == 'dropshipping'){
				$methodDetail = $rate->getMethodDetail();
				$this->getQuote()->getShippingAddress()
				->setShippingMethodDetail($methodDetail);
			}
		}
		return $result;
    }
}
