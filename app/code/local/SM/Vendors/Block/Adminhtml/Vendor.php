<?php

class SM_Vendors_Block_Adminhtml_Vendor extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_vendor';
        $this->_blockGroup = 'smvendors';
        $this->_headerText = Mage::helper('smvendors')->__('Vendor Manager');
        $this->_addButtonLabel = Mage::helper('smvendors')->__('Add new vendor');
        parent::__construct();
    }
}