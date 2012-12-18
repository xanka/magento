<?php

class SM_Vendors_Controller_Adminhtml_Action extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        if (!Mage::helper('smcore')->checkLicense("X-MultiVendor Basic", Mage::getStoreConfig('smvendors/general/key'))) {
            return false;
        }        
        return true;
    }    
}