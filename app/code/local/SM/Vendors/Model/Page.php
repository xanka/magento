<?php

class SM_Vendors_Model_Page extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('smvendors/page');
    }
	
	public function getPageContent(){
        $content = $this->getData('content');
		$processor = Mage::getModel('core/email_template_filter');
		$content = $processor->filter($content);
        return $content;
    }
    
    protected function _beforeSave(){
    	$this->setData('active','1');
    	return parent::_beforeSave();
    }
}