<?php

class Company_Web_Block_Adminhtml_Answer_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('answer_form', array('legend'=>Mage::helper('web')->__('Answer information')));

//      $model = Mage::registry('partner_edit');
     
//      $fieldset->addField('question', 'text', array(
//          'label'     => Mage::helper('web')->__('Company'),
//          'class'     => 'required-entry',
//          'required'  => true,
//          'name'      => 'question',
//      ));


//      $fieldset->addField('website', 'text', array(
//          'label'     => Mage::helper('web')->__('Website'),
//          'class'     => 'required-entry',
//          'required'  => true,
//          'name'      => 'website',
//      ));

//      $fieldset->addField('content', 'editor', array(
//          'name'      => 'content',
//          'label'     => Mage::helper('web')->__('Content'),
//          'title'     => Mage::helper('web')->__('Content'),
//          'style'     => 'width:700px; height:500px;',
//          'wysiwyg'   => false,
//          'required'  => true,
//      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getWebData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getWebData());
          Mage::getSingleton('adminhtml/session')->setWebData(null);
      } elseif ( Mage::registry('web_data') ) {
          $form->setValues(Mage::registry('web_data')->getData());
      }
      return parent::_prepareForm();
  }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('web')->__('Answer Information');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('web')->__('Answer Information');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}