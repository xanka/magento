<?php

class SM_Vendors_Model_Banner extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('smvendors/banner');
    }
    public function getImageUrl(){
    	$imageUrl = '';
    	if($this->getImage()){
    		$imageUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$this->getImage();
    	}
    	return $imageUrl;
    }
    
    public function getWidth(){
        return intval($this->getData('width'));
    }
    
    public function getHeight(){
        return intval($this->getData('height'));
    }
}