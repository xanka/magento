<?php

class SM_Core_Model_Observer extends Mage_Core_Model_Abstract {

    // refresh license status after updating
    public function refreshStatus($observer) {

        ob_start();
        $product = split("_", $observer['event']['name']);
        $product = $product[count($product) - 1];

        // remove old local key
        $dir = Mage::getBaseDir("var") . DS . "smartosc" . DS . strtolower(substr('X-MultiVendor Basic', 0, 5)) . DS;
        $filepath = $dir . "license.dat";
        $file = new Varien_Io_File;
        $file->rm($filepath);
        if (Mage::helper('smcore')->checkLicense('X-MultiVendor Basic', Mage::getStoreConfig($product . '/general/key'), true))
            Mage::getModel('core/config')->saveConfig($product . '/general/enable', "1");
        else
            Mage::getModel('core/config')->saveConfig($product . '/general/enable', "0");

        Mage::getConfig()->cleanCache();
    }

}
