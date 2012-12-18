<?php
class Company_Web_Block_Adminhtml_Question extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_question';
    $this->_blockGroup = 'web';
    $this->_headerText = Mage::helper('web')->__('Question Manager');
    $this->_addButtonLabel = Mage::helper('web')->__('Add Question');
    parent::__construct();
  }
}