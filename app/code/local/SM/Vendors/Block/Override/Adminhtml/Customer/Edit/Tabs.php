<?php

class SM_Vendors_Block_Override_Adminhtml_Customer_Edit_Tabs extends Mage_Adminhtml_Block_Customer_Edit_Tabs
{

    protected function _beforeToHtml()
    {
        $this->addTab('customer_groups', array(
            'label'     => Mage::helper('customer')->__('Customer Group'),
            'content'   => $this->getLayout()->createBlock('smvendors/override_adminhtml_customer_edit_tab_group')->initForm()->toHtml(),
        ));
    	return parent::_beforeToHtml();
    }
    
    public function addTab($tabId, $tab){
  
    	$tabsAllow = array();
    	
    	if($vendor = Mage::helper('smvendors')->getVendorLogin()){
    		$tabsAllow = array(
    			'customer_groups'
    		);
    	}
    	else{
    		$tabsAllow = array(
    			'account',
    			'addresses',
    			'orders',
    			'cart',
    			'wishlist',
    			'newsletter',
    			'reviews',
    			'tags'
    		);
    	}
    	
    	if(in_array($tabId, $tabsAllow)){
    		return parent::addTab($tabId, $tab);
    	}
    	return $this;
    }
   
}
