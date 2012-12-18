<?php
class SM_Dropship_Block_Override_Checkout_Cart extends SM_Vendors_Block_Override_Checkout_Cart
{
	/**
	 * 
	 * @var Array
	 */
	protected $_rates;
	
	/**
	 * 
	 * @var Array
	 */
	protected $_vendorTotals = array();
	
	/**
	 * 
	 * @param int $vendorId
	 * return boolean
	 */
	public function getDropShipRatesByVendor($vendorId)
	{
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
	 * @param int $vendorId
	 * @return string
	 */
	public function getDropShippingHtml($vendorId)
	{
		$rates = $this->getDropShipRatesByVendor($vendorId);
		
		if (!empty($rates)) {
			$html = array();
			$html[] = '<select class="shipping_method_by_vendor" name="shipping_method_vendor['.$vendorId.']" id="shipping_method_v'.$vendorId.'" vendor_id="'.$vendorId.'">';
			
			foreach ($rates as $rate) {
				$rate->setPriceFormat(Mage::helper('core')->currency($rate->getPrice(),true,false));
				$rate->setCode('v'.$vendorId.'_'.$rate->getCarrier().'_'.$rate->getMethod());
				$rate->setTitle($rate->getCarrierTitle().' - '.$rate->getMethodTitle());
				$html[] = '<option value="'.$rate->getCode().'">'.$rate->getTitle().'</option>';
			}
			
			$html[]='</select>';
			$firstRate = reset($rates);
			$html[]='<span id="shipping_method_price_vendor_'.$vendorId.'">'.$firstRate->getPriceFormat().'</span>';
			return implode("\n", $html);
		}
		
		return '';
	}

	/**
	 * 
	 * @return string
	 */
	public function getShippingConfigToJson()
	{
		$vendorItems = $this->getVendorItems();
		$result = array();
		$result['shipping_methods_by_vendor'] = array();
		$result['selected_shipping'] = Mage::getSingleton('checkout/cart')->getQuote()->getShippingAddress()->getShippingMethod();

		foreach ($vendorItems as $vendorId => $item) {
			$vendorId = intval($vendorId);
			if (!empty($vendorId)) {
				if (empty($result[$vendorId])) {
					$result['shipping_methods_by_vendor'][$vendorId] = array();
				}
				
				$rates = $this->getDropShipRatesByVendor($vendorId);
				
				foreach ($rates as $rate) {
					$result['shipping_methods_by_vendor'][$vendorId][$rate->getCode()] = $rate->getData();
				}
			}
		}
		ksort($result['shipping_methods_by_vendor']);

		return json_encode($result);
	}

	/**
	 * 
	 * @return Array
	 */
	public function getShippingPriceByVendor($vendorId)
	{
		$vendorItems = $this->getVendorItems();
		$result = array();
		$result['shipping_methods_by_vendor'] = array();
		$selectedShippingMethod = Mage::getSingleton('checkout/cart')->getQuote()->getShippingAddress()->getShippingMethod();
		$selectedShippingMethod = str_replace('dropshipping_', '', $selectedShippingMethod);
		$selectedShippingMethods = explode('|', $selectedShippingMethod);
		
		$rates = $this->getDropShipRatesByVendor($vendorId);
		$shippingPrice = 0;
		foreach ($rates as $rate) {
			if(in_array($rate->getCode(), $selectedShippingMethods)){
				$shippingPrice = $rate->getPrice();
				break;
			}
		}
		return $shippingPrice;
	}
	
	/**
	 * 
	 * @return Array
	 */
	public function getEstimateRates()
	{
		if (empty($this->_rates)) {
			$groups = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress()->getGroupedAllShippingRates();
			$this->_rates = $groups;
		}
		
		return $this->_rates;
	}
	
	
	public function getVendorTotals($vendorId)
	{
		if(empty($this->_vendorTotals[$vendorId])){
			$vendorItems = $this->getVendorItems();
			if(!empty($vendorItems[$vendorId])){
				$vendorItems = $vendorItems[$vendorId];
				
				if (empty($this->_vendorTotals[$vendorId])) {
					$this->_vendorTotals[$vendorId] = array(
						'shipping_price' => 0,
						'subtotal' => 0,
						'tax'	=>0
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
}
