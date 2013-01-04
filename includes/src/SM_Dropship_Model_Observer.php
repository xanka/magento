<?php

class SM_Dropship_Model_Observer {

    public function hookSplitOrderBefore($observer) {
        $order = $observer->getOrder();
        /* @var $order Mage_Sales_Model_Order */
        $quote = $order->getQuote();
        /* @var $quote Mage_Sales_Model_Quote */

        $shippingMethod = $order->getShippingMethod();

        if (Mage::helper('smdropship')->dropShipIsActive()) {
            $rate = $quote->getShippingAddress()->getShippingRateByCode($shippingMethod);
            if ($rate->getCarrier() == 'dropshipping') {
                $methodDetail = $rate->getMethodDetail();
                $quote->getShippingAddress()
                        ->setShippingMethodDetail($methodDetail);
                $shippingMethodDetail = $quote->getShippingAddress()->getShippingMethodDetail();
                $order->setShippingMethodDetail($shippingMethodDetail);
                $order->save();
            }
        }

        return $this;
    }

    public function hookVendorEditTabPrepareForm($observer) {
        $fieldset = $observer->getFieldset();
        $customer = Mage::registry('current_customer');
        if ($this->isElementAllow('vendor_shipping_methods')) {
        	/*
        	$fieldset->addField('vendor_shipping_methods', 'multiselect', array(
        			'label' => Mage::helper('smdropship')->__('Shipping methods'),
        			'name' => 'vendor[vendor_shipping_methods]',
        			'values' => Mage::helper('smdropship')->getAllShippingMethodToOptionArray(),
        	));
        	*/
        	/* hieptq hardcode */
        	$fieldset->addField('vendor_shipping_methods', 'hidden', array(
        			'label' => Mage::helper('smdropship')->__('Shipping methods'),
        			'name' => 'vendor[vendor_shipping_methods][]',
        			'value' => 'vendormultiflatrate'
        	));
        	$customer->setData('vendor_shipping_methods','vendormultiflatrate');
        }
        
        if($vendorId = $customer->getVendorId()){
	        
	        
	        if ($this->isElementAllow('vendor_shipping_rate_free')) {
	        	
	        	$rates = Mage::getResourceModel('smdropship/shipping_multiflatrate_collection')->addFreeToFilter();
	        	$value = '';
	        	
	        	foreach($rates as $rate){
	        		$vendorIds = $rate->getVendorIds();
	        		if(in_array($vendorId, $vendorIds)){
	        			$value = $rate->getId();
	        			break;
	        		}
	        	}
	        	
	        	$customer->setData('vendor_shipping_rate_free',$value);
	        	
	        	$fieldset->addField('vendor_shipping_rate_free', 'select', array(
	        			'label' => Mage::helper('smdropship')->__('Free Delivery'),
	        			'name' => 'vendor[vendor_shipping_rate_free]',
	        			'values' => Mage::helper('smdropship')->getAllShippingRateFreeToOptionArray(),
	        	));
	        }
	        
	        if ($this->isElementAllow('vendor_shipping_rate_nofree')) {
	        	
	        	$rates = Mage::getResourceModel('smdropship/shipping_multiflatrate_collection')->addNoFreeToFilter();
	        	$value = array();
	        	
	        	foreach($rates as $rate){
	        		$vendorIds = $rate->getVendorIds();
	        		if(in_array($vendorId, $vendorIds)){
	        			$value[] = $rate->getId();
	        		}
	        	}
				
	        	$customer->setData('vendor_shipping_rate_nofree',implode(',', $value));
	        	
	        	$fieldset->addField('vendor_shipping_rate_nofree', 'multiselect', array(
	        			'label' => Mage::helper('smdropship')->__('Delivery Costs'),
	        			'name' => 'vendor[vendor_shipping_rate_nofree]',
	        			'value' => $value,
	        			'values' => Mage::helper('smdropship')->getAllShippingRateNoFreeToOptionArray(),
	        	));
	        }
        
        }
    }

    public function hookAdminCustomerPrepareSave($observer){
    	$customer = $observer->getCustomer();
    	$request = $observer->getRequest();
    	$vendorData = $request->getPost('vendor');
    	if($vendorId = $customer->getVendorId()){
	    	if(isset($vendorData['vendor_shipping_rate_free'])){
	    		
	    		$rates = Mage::getResourceModel('smdropship/shipping_multiflatrate_collection')->addFreeToFilter();
	    		foreach($rates as $rate){
	    			$vendorIds = $rate->getVendorIds();
	    			if($rate->getId() == $vendorData['vendor_shipping_rate_free']){
	    				$vendorIds[$vendorId] = $vendorId;
	    			}
	    			else{
	    				unset($vendorIds[$vendorId]);
	    			}
		    		
		    		$vendorIds = implode(',',$vendorIds);
		    		$rate->setVendorIds($vendorIds);
		    		$rate->save();
	    		}
	    	}
	    	
	    	if(isset($vendorData['vendor_shipping_rate_nofree'])){
	    		 
	    		$rates = Mage::getResourceModel('smdropship/shipping_multiflatrate_collection')->addNoFreeToFilter();
	    		foreach($rates as $rate){
	    			$vendorIds = $rate->getVendorIds();
	    			
	    			if(in_array($rate->getId(), $vendorData['vendor_shipping_rate_nofree'])){		
	    				$vendorIds[$vendorId] = $vendorId;
	    			}
	    			else{
	    				unset($vendorIds[$vendorId]);
	    			}
	    			
	    			$vendorIds = implode(',',$vendorIds);
	    			$rate->setVendorIds($vendorIds);
	    			$rate->save();
	    		}
	    	}
    	}
    }
    
	public function isElementAllow($elementName)
	{
		$vendorElementAllow = array(
				'vendor_name',
				'vendor_sale_postcodes',
				'vendor_logo',
//				'vendor_shipping_methods',
				'vendor_contact_email',
				'vendor_shipping_rate_nofree',
				'vendor_shipping_rate_free'
		);

		if ($vendor = $this->getVendorLogin()) {
			return in_array($elementName, $vendorElementAllow);
		}
		// admin allow true
		return true;
	}    
	public function getVendorLogin()
	{
		if (empty($this->_vendorLogin)) {
			$this->_vendorLogin = Mage::helper('smvendors')->getVendorLogin();
		}
		
		return $this->_vendorLogin;
	}        

}