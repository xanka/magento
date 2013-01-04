<?php
class SM_Vendors_Block_Adminhtml_Sales_Order_Totals extends Mage_Adminhtml_Block_Sales_Totals
{
    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
        parent::_initTotals();
        
        $vendorOrder = Mage::registry('vendor_order');
        if(!$vendorOrder) {
            $vendorOrder = $this->getParentBlock()->getVendorOrder();
        }
        
        if($vendorOrder) {
            $this->setVendorOrder($vendorOrder);
            $this->_updateTotalForVendor('subtotal', $vendorOrder, 'subtotal');
            $this->_updateTotalForVendor('shipping', $vendorOrder, 'shipping_amount');
            $this->_updateTotalForVendor('discount', $vendorOrder, 'discount_amount', $this->helper('sales')->__('Discount'));
            $this->_updateTotalForVendor('grand_total', $vendorOrder, 'grand_total');
        }
        
        return $this;
    }
    
    protected function _updateTotalForVendor($total, $vendorOrder, $key, $label = false)
    {
        if(isset($this->_totals[$total]) && is_object($this->_totals[$total])) {
            $this->_totals[$total]->setData('value', $vendorOrder->getData($key));
            $this->_totals[$total]->setData('base_value', $vendorOrder->getData($key));
            if($label) {
                $this->_totals[$total]->setData('label', $label);
            }
        }
    }
}
