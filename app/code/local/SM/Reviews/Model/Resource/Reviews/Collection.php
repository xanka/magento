<?php

class SM_Reviews_Model_Resource_Reviews_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('smreviews/reviews');
    }

}
