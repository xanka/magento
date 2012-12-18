<?php
class Company_Web_Block_Adminhtml_Answer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_answer';
    $this->_blockGroup = 'web';
    $this->_headerText = Mage::helper('web')->__('Answer View');
//    $this->_addButtonLabel = Mage::helper('web')->__('Add Answer');

    parent::__construct();
      $this->removeButton('add');

  }
}