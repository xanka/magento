<?php
class SM_Dropship_Model_Override_Shipping_Shipping extends Mage_Shipping_Model_Shipping
{
	public function collectRates2(Mage_Shipping_Model_Rate_Request $request)
    {
        $storeId = $request->getStoreId();
        if (!$request->getOrig()) {
            $request
                ->setCountryId(Mage::getStoreConfig(self::XML_PATH_STORE_COUNTRY_ID, $request->getStore()))
                ->setRegionId(Mage::getStoreConfig(self::XML_PATH_STORE_REGION_ID, $request->getStore()))
                ->setCity(Mage::getStoreConfig(self::XML_PATH_STORE_CITY, $request->getStore()))
                ->setPostcode(Mage::getStoreConfig(self::XML_PATH_STORE_ZIP, $request->getStore()));
        }

        $limitCarrier = $request->getLimitCarrier();
		$carriers = Mage::getStoreConfig('carriers', $storeId);
		
		foreach ($carriers as $carrierCode => $carrierConfig) {
			if(!in_array($carrierCode,$limitCarrier)){
				$this->collectCarrierRates($carrierCode, $request);
				
			}
			//echo $carrierCode;
		}
		
        return $this;
    }
}
