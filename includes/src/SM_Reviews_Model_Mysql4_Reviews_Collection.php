<?php

class SM_Reviews_Model_Mysql4_Reviews_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        $this->_init('smreviews/reviews');
    }
}
