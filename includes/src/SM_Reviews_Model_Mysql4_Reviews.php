<?php

class SM_Reviews_Model_Mysql4_Reviews extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('smreviews/reviews', 'review_id');
    }

}
