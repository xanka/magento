<?php
class SM_Vendors_Block_Adminhtml_Sales_Order_Create_Customer_Grid extends Mage_Adminhtml_Block_Sales_Order_Create_Customer_Grid
{
    protected function _prepareCollection()
    {
        if($vendor = Mage::helper('smvendors')->getVendorLogin()) {
        	$user = Mage::getSingleton('admin/session')->getUser();
            $grandParent = get_parent_class(get_parent_class($this));
            $collection = Mage::getResourceModel('customer/customer_collection')
                ->addNameToSelect()
                ->addAttributeToSelect('email')
                ->addAttributeToSelect('created_at')
                ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
                ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
                ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
                ->joinAttribute('billing_regione', 'customer_address/region', 'default_billing', null, 'left')
                ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left')
                ->joinField('store_name', 'core/store', 'name', 'store_id=store_id', null, 'left')
                ->joinField('vendor_customer', 'smvendors/vendor_customer', null, 'customer_id=entity_id', "{{table}}.vendor_id={$vendor->getId()}", 'inner')
                ;
            $this->setCollection($collection);
            return call_user_func(array($grandParent, '_prepareCollection'));
        } else {
            return parent::_prepareCollection();
        }
        return parent::_prepareCollection();
    }
}
