<?php

class SM_Dropship_Block_Override_Adminhtml_Sales_Order_Create_Shipping_Method_Form
    extends Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form
{
   

    /**
     * Retrieve array of shipping rates groups
     *
     * @return array
     */
    public function getShippingRates()
    {
        if(Mage::helper('smdropship')->dropShipIsActive()){
			if (empty($this->_rates)) {
				$groups = $this->getAddress()->getGroupedAllShippingRates();
				$dropShipCode = 'dropshipping';
				if (!empty($groups) AND array_key_exists($dropShipCode,$groups)) {
					return $this->_rates = array($dropShipCode => $groups[$dropShipCode]);
				}
			}
			return $this->_rates;
		}
		return parent::getShippingRates();
    }
}
