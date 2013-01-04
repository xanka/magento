<?php
require_once('Mage' . DS . 'Adminhtml' . DS . 'controllers' . DS . 'CustomerController.php');

class SM_Vendors_Controller_Adminhtml_Customer extends Mage_Adminhtml_CustomerController
{
    protected function _isAllowed()
    {
        if (!Mage::helper('smcore')->checkLicense("X-MultiVendor Basic", Mage::getStoreConfig('smvendors/general/key'))) {
            return false;
        }        
        return true;
    }    
}