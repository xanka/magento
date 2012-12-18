<?php

class SM_Vendors_Helper_Banner extends Mage_Core_Helper_Abstract
{
    public function getVendorBannerPosition($vendor,$position){
    	$banners = Mage::getResourceModel('smvendors/banner_collection')->addVendorToFilter($vendor->getId());
    	//echo $banners->getSelect();
    	$result = array();
    	foreach($banners as $banner){
    		$positions = $banner->getPosition();
    		$positions = explode(',', $positions);
    		if(in_array($position, $positions)){
    			$result[] = $banner;
    		}
    	}
    	return $result;
    }
}