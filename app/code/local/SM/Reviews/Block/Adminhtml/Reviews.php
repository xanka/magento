<?php

class SM_Reviews_Block_Adminhtml_Reviews extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_reviews';
        $this->_blockGroup = 'smreviews';
        $this->_headerText = Mage::helper('smreviews')->__('Reviews Manager');
        parent::__construct();
        $this->_removeButton('add');
    }

}