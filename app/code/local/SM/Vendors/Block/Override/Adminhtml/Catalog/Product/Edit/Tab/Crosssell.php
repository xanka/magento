<?php

class SM_Vendors_Block_Override_Adminhtml_Catalog_Product_Edit_Tab_Crosssell extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Crosssell
{
    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        /* @var $collection Mage_Catalog_Model_Resource_Product_Link_Product_Collection */
        $collection = Mage::getModel('catalog/product_link')->useCrossSellLinks()
            ->getProductCollection()
            ->setProduct($this->_getProduct())
            ->addAttributeToSelect('*');

        if ($this->isReadonly()) {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = array(0);
            }
            $collection->addFieldToFilter('entity_id', array('in' => $productIds));
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
