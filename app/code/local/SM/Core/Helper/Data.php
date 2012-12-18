<?php

class SM_Core_Helper_Data extends Mage_Core_Helper_Abstract {

    public function checkLicense($product, $key, $update = false) {
        return true;
    }

    protected function _checkLicense($licensekey, $localkey = "") {
        return true;
    }

    public function checkUpdate() {
    }

}

?>
