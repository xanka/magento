<?php

class SM_Vendors_Block_Override_Adminhtml_Customer extends Mage_Adminhtml_Block_Customer
{

    public function __construct()
    {
        parent::__construct();
        if($vendor = Mage::helper('smvendors')->getVendorLogin()){
        	$this->removeButton('add');
        	
        }
    }

}
