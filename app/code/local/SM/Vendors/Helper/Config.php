<?php
class SM_Vendors_Helper_Config extends Mage_Core_Helper_Abstract
{
	public function getStoreConfigFlag($path , $store , $vendor){
	
        $flag = strtolower($this->getStoreConfig($path, $store,$vendor));
        if (!empty($flag) && 'false' !== $flag) {
            return true;
        } else {
            return false;
        }
	}
	
	public function getStoreConfig($path , $store , $vendor){
		$model = Mage::getModel('smvendors/config_core');
		$data = '';
                    $model->setPath($path)
                        ->setValue($data)
                        ->setWebsite('default')
                        ->setStore($store)
                        ->setVendorId($vendor)
                        ->afterLoad();
                    $data = $model->getValue();
                    return $data;
	}
}