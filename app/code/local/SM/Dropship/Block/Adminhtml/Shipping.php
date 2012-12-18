<?php

class SM_Dropship_Block_Adminhtml_Shipping extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_shipping';
        $this->_blockGroup = 'smdropship';
        $this->_headerText = Mage::helper('smdropship')->__('Shipping Manager');
        $this->_addButtonLabel = Mage::helper('smdropship')->__('Add New Rate');
        parent::__construct();
    }
}