<?php

class Company_Web_Model_Question extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('web/question');
    }
}