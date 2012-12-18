<?php

class SM_Vendors_Block_Override_Adminhtml_Catalog_Product_Edit_Tab_Super_Config_Grid extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Grid
{


    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Grid
     */
    protected function _prepareCollection()
    {
        $allowProductTypes = array();
        foreach (Mage::helper('catalog/product_configuration')->getConfigurableAllowedTypes() as $type) {
            $allowProductTypes[] = $type->getName();
        }

        $product = $this->_getProduct();
        $collection = $product->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->addAttributeToSelect('price')
            ->addFieldToFilter('attribute_set_id',$product->getAttributeSetId())
            ->addFieldToFilter('type_id', $allowProductTypes)
            ->addFilterByRequiredOptions()
            ->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'inner');

        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            Mage::getModel('cataloginventory/stock_item')->addCatalogInventoryToProductCollection($collection);
        }

        foreach ($product->getTypeInstance(true)->getUsedProductAttributes($product) as $attribute) {
            $collection->addAttributeToSelect($attribute->getAttributeCode());
            $collection->addAttributeToFilter($attribute->getAttributeCode(), array('notnull'=>1));
        }

		if($vendor = Mage::helper('smvendors')->getVendorLogin()){
			$collection->addAttributeToSelect('sm_product_vendor_id')
			->addAttributeToFilter('sm_product_vendor_id',$vendor->getId());
		}
		
        $this->setCollection($collection);

        if ($this->isReadonly()) {
            $collection->addFieldToFilter('entity_id', array('in' => $this->_getSelectedProducts()));
        }
		$grandParent = $this->getGrandParent();
        call_user_func(array($grandParent, '_prepareCollection'));
        return $this;
    }
	
	public function getGrandParent(){
		$grandParent = get_parent_class(get_parent_class($this));
		return $grandParent;
	}
}
