<?php

class SM_Dropship_Model_Mysql4_Shipping_Multiflatrate_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('smdropship/shipping_multiflatrate');
    }

    public function addNoFreeToFilter() {
        $this->addFilter('type', 'nofree');
        return $this;
    }

    public function addFreeToFilter() {
        $this->addFilter('type', 'free');
        return $this;
    }

    public function toOptionArray() {
        $result = array();
        $result[] = array('label' => '', 'value' => '');
        $label = '';
        foreach ($this as $item) {
            if ($item->getTitle() == 'Free Shipping') {
                $label = 'Free Delivery';
            } else {
                $label = $item->getTitle();
            }
            $result[] = array(
                'label' => $label,
                'value' => $item->getId()
            );
        }
        return $result;
    }

}