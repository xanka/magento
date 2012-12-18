<?php

class SM_Dropship_Model_Carrier_DropShip extends Mage_Shipping_Model_Carrier_Abstract
{
    /* Use group alias */
    protected $_code = 'dropshipping';

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        // skip if not enabled
        if (!$this->isActive())
            return false;

        $result = Mage::getModel('shipping/rate_result');
        $handling = 0;
        if(Mage::getStoreConfig('carriers/'.$this->_code.'/handling') >0)
            $handling = Mage::getStoreConfig('carriers/'.$this->_code.'/handling');
        if(Mage::getStoreConfig('carriers/'.$this->_code.'/handling_type') == 'P' && $request->getPackageValue() > 0)
            $handling = $request->getPackageValue()*$handling;

		$requestClone = clone $request;
		$result = $this->getResult($request);
        return $result; // it doesnt do anything if there was an error just returns blank - maybe there should be a default shipping incase of problem? or email sysadmin?
    }

	public function getResult($request){
		$vendors = array();
		if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
				$product = Mage::getModel('catalog/product')->load($item->getProduct()->getId());
				$vendorId = (int)$product->getData('sm_product_vendor_id');
				if(empty($vendors[$vendorId])){
					$vendor = Mage::getModel('smvendors/vendor')->load($vendorId);
					$vendors[$vendorId] = array(
						'vendor' => $vendor,
						'items' => array(),
						'shipping_methods' => array()
					);
				}
				$vendors[$vendorId]['items'][] = $item;
            }
        }
		ksort($vendors);
		
		$shippingMethods = array();
		$i=0;
		foreach($vendors as $vendor){
			$availableShippingMethods = $vendor['vendor']->getAvaiableShippingMethods();
			$requestVendorClone = clone $request;
			$requestVendorClone->setAllItems($vendor['items']);
			$requestVendorClone->setLimitCarrier(array($this->_code));
			$requestVendorClone->setVendorId($vendor['vendor']->getId());
			$packageQty  = 0;
			$packageWeight=0;
			foreach($vendor['items'] as $item){
				$packageQty += $item->getQty();
				$packageWeight += $item->getWeight();
			}
			$requestVendorClone->setPackageQty($packageQty);
			$requestVendorClone->setPackageWeight($packageWeight);
			$shipping = Mage::getModel('shipping/shipping')->collectRates2($requestVendorClone);
			foreach($shipping->getResult()->getAllRates() as $rate){
				if(in_array($rate->getCarrier(),$availableShippingMethods)){
					$vendors[$vendor['vendor']->getId()]['shipping_methods'][] = $rate;
					
					if(empty($shippingMethods[$i])){
						$shippingMethods[$i] = array();
					}
//					if(!in_array($title,$shippingArr[$i])){
						$shippingMethods[$i][] =  array(
							'vendor' => $vendor['vendor'],
							'rate' => $rate
						);
//					}
				}
			}
			$i++;
		}
		
		if(!function_exists('array_cartesian')){
		
			function array_cartesian($arrays) {
				$result = array();
				$keys = array_keys($arrays);
				$reverse_keys = array_reverse($keys);
				$size = intval(count($arrays) > 0);
				foreach ($arrays as $array) {
					$size *= count($array);
				}
				for ($i = 0; $i < $size; $i ++) {
					$result[$i] = array();
					foreach ($keys as $j) {
						$result[$i][$j] = current($arrays[$j]);
					}
					foreach ($reverse_keys as $j) {
						if (next($arrays[$j])) {
							break;
						}
						elseif (isset ($arrays[$j])) {
							reset($arrays[$j]);
						}
					}
				}
				return $result;
			}
		}
		$shippingMethodsCartesian = array_cartesian($shippingMethods);
		$methods = Mage::getModel('shipping/rate_result');
		foreach($shippingMethodsCartesian as $shippingRates){
			$methodTitle = array();
			$methodCode = array();
			$methodCost = 0;
			$methodPrice = 0;
			
			$methodDetail = array();
			
			foreach($shippingRates as $rate){
				$methodTitle[] = $rate['vendor']->getVendorName().' '.$rate['rate']->getCarrierTitle().' - ' . $rate['rate']->getMethodTitle();
				$methodCode[] = 'v'.$rate['vendor']->getId().'_'.$rate['rate']->getCarrier().'_' . $rate['rate']->getMethod();
				$methodPrice += $rate['rate']->getPrice();
				$methodCost += $rate['rate']->getCost();
				
				$methodDetail[$rate['vendor']->getId()] = array(
					'method' => $rate['rate']->getData(),
					'vendor' => $rate['vendor']->getData()
				);
			}
			$methodTitle  = implode('<br/>', $methodTitle);
			$methodCode  =  implode('|', $methodCode);
			$method = Mage::getModel('shipping/rate_result_method');
			$method->setCarrier($this->_code);
			$method->setCarrierTitle(Mage::getStoreConfig('carriers/'.$this->_code.'/title'));
			$method->setMethod('dropshipping');
			$method->setMethodTitle($methodTitle);
			$method->setMethod($methodCode);
			$method->setCost($methodCost);
			$method->setPrice($methodPrice);
			$method->setData('shipping_rates_by_vendors',$vendors);
			// set shipping method detail for each shipping rate
			$methodDetail = serialize($methodDetail);
			$method->setMethodDetail($methodDetail);
			$methods->append($method);
		}
		return $methods;
	}
	
}
?>