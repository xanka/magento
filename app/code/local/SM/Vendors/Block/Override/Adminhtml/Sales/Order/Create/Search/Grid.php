<?php

class SM_Vendors_Block_Override_Adminhtml_Sales_Order_Create_Search_Grid extends Mage_Adminhtml_Block_Sales_Order_Create_Search_Grid
{
    /**
     * Prepare collection to be displayed in the grid
     *
     * @return Mage_Adminhtml_Block_Sales_Order_Create_Search_Grid
     */
    protected function _prepareCollection()
    {
        $attributes = Mage::getSingleton('catalog/config')->getProductAttributes();
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection
            ->setStore($this->getStore())
            ->addAttributeToSelect($attributes)
            ->addAttributeToSelect('sku')
            ->addStoreFilter()
            ->addAttributeToFilter('type_id', array_keys(
                Mage::getConfig()->getNode('adminhtml/sales/order/create/available_product_types')->asArray()
            ))
            ->addAttributeToSelect('gift_message_available');

        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($collection);
		
		if($vendor = Mage::helper('smvendors')->getVendorLogin()){
			$collection->addAttributeToSelect('sm_product_vendor_id')
			->addAttributeToFilter('sm_product_vendor_id',$vendor->getId());
		}
        $this->setCollection($collection);
		$grandParent = $this->getGrandParent();
        return call_user_func(array($grandParent, '_prepareCollection'));
    }
	
	public function getGrandParent(){
		$grandParent = get_parent_class(get_parent_class($this));
		return $grandParent;
	}
}
