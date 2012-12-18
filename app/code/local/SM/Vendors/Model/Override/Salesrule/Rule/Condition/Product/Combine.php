<?php

class SM_Vendors_Model_Override_Salesrule_Rule_Condition_Product_Combine extends Mage_SalesRule_Model_Rule_Condition_Product_Combine
{
    public function validate(Varien_Object $object)
    {
        if($vendorId = $this->getRule()->getData('vendor_id')) {
            $product = false;
            if ($object->getProduct() instanceof Mage_Catalog_Model_Product) {
                $product = $object->getProduct();
        
                if(!$product || !$product->getData('sm_product_vendor_id')) {
                    $product = Mage::getModel('catalog/product')
                    ->load($object->getProductId());
                }
                
                if($product->getData('sm_product_vendor_id') != $vendorId) {
                    return false;
                }
            }
        }
        
        return parent::validate($object);
    }
}
