<?php
/**
 * Date: 10/27/12
 * Time: 2:39 AM
 */

class SM_Planet_Model_Resource_Partner_Collection extends  Mage_Core_Model_Resource_Db_Collection_Abstract {
    protected function _construct() {
        $this->_init('planet/partner');
    }
}