<?php
class SM_Vendors_Block_Override_Adminhtml_Page_Menu  extends Mage_Adminhtml_Block_Page_Menu
{
	
    public function getMenuArray()
    {
        //Load standard menu
        
        $parentArr = parent::getMenuArray();
        
		if($vendor = Mage::helper('smvendors')->getVendorLogin()){
			//unset($parentArr['smvendors']['children']['vendors_manager']);
			if(isset($parentArr['smvendors'])){
				$parentArr['smvendors']['label'] = $this->__('My Account');
			}
			
			if(isset($parentArr['smvendors']['children']['vendors_page'])){
				$parentArr['smvendors']['children']['vendors_page']['label'] = $this->__('My Page');
			}
		}
		else{
			unset($parentArr['smvendors']['children']['vendors_profile']);
		}
        return $parentArr;
    }
}
