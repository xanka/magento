<?php

class SM_Reviews_Block_Adminhtml_Reviews_Edit_Rating extends Varien_Data_Form_Element_Abstract {

    public function __construct($data) {
        parent::__construct($data);
        $this->setType('rating');
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getElementHtml() {
        $html = '';

        if ($this->getValue()) {
        $val = intval($this->getValue());
        $html = "";
        for ($i=0; $i<$val; $i++):
            $html .= "<img src='".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN)."adminhtml/default/default/images/product_rating_full_star.gif' />";
        endfor;
        for ($i=0; $i<5-$val; $i++):
            $html .= "<img src='".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN)."adminhtml/default/default/images/product_rating_blank_star.gif' />";
        endfor;    
        }
//        $this->setClass('input-file');
//        $html.= parent::getElementHtml();

        return $html;
    }


}