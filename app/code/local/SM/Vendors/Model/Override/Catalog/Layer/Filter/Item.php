<?php
class SM_Vendors_Model_Override_Catalog_Layer_Filter_Item extends Mage_Catalog_Model_Layer_Filter_Item
{
    /**
     * Get filter item url
     *
     * @return string
     */
    public function getUrl()
    {
		$query = array(
			$this->getFilter()->getRequestVar()=>$this->getValue(),
			Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
		);
		
		if (Mage::registry('in_vendor')) {
			$vendor = Mage::registry('current_vendor');
			
			return Mage::getUrl($vendor->getVendorSlug().'/products', 
					array('_current' => true, '_query'=>$query));
		}
		
		return Mage::getUrl('*/*/*', array('_current'=>true, '_use_rewrite'=>true, '_query'=>$query));
			
    }

    /**
     * Get url for remove item from filter
     *
     * @return string
     */
    public function getRemoveUrl()
    {
        $query = array($this->getFilter()->getRequestVar()=>$this->getFilter()->getResetValue());
        $params['_current']     = true;
        $params['_query']       = $query;
        $params['_escape']      = true;

        if (Mage::registry('in_vendor')) {
        	$vendor = Mage::registry('current_vendor');
        
        	return Mage::getUrl($vendor->getVendorSlug().'/products', $params);
        }
        
        $params['_use_rewrite'] = true;
        $reader = new XMLReader();
        return Mage::getUrl('*/*/*', $params);
    }
}