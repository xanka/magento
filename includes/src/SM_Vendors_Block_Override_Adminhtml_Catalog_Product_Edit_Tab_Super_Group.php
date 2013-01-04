<?php
class SM_Vendors_Block_Override_Adminhtml_Catalog_Product_Edit_Tab_Super_Group extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Group
{
    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Group
     */
    protected function _prepareCollection()
    {
        $allowProductTypes = array();
        $allowProductTypeNodes = Mage::getConfig()
            ->getNode('global/catalog/product/type/grouped/allow_product_types')->children();
        foreach ($allowProductTypeNodes as $type) {
            $allowProductTypes[] = $type->getName();
        }

        $collection = Mage::getModel('catalog/product_link')->useGroupedLinks()
            ->getProductCollection()
            ->setProduct($this->_getProduct())
            ->addAttributeToSelect('*')
            ->addFilterByRequiredOptions()
            ->addAttributeToFilter('type_id', $allowProductTypes);

        if ($this->getIsReadonly() === true) {
            $collection->addFieldToFilter('entity_id', array('in' => $this->_getSelectedProducts()));
        }
		
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
