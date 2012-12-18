<?php

class Company_Web_Block_Adminhtml_Answer_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('answer_tabs');
      $this->setDestElementId('answer_form');
      $this->setTitle(Mage::helper('web')->__('Answer Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('web')->__('Answer Information'),
          'title'     => Mage::helper('web')->__('Answer Information'),
          'content'   => $this->getLayout()->createBlock('web/adminhtml_answer_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}