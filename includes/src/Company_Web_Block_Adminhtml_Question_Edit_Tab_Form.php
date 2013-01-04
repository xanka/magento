<?php

class Company_Web_Block_Adminhtml_Question_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('question_form', array('legend'=>Mage::helper('web')->__('Question information')));

//      $model = Mage::registry('partner_edit');
      $params = $this->getRequest()->getParams();
      $model = Mage::getModel('web/question')->load($params['id']);
      $fieldset->addField('question_type', 'select', array(
          'label'     => Mage::helper('web')->__('Question type'),
          'name'      => 'question_type',

          'values'    => array(
              array(
                  'value'     => 'dropbox',
                  'label'     => Mage::helper('web')->__('Drop Down Box'),
              ),

              array(
                  'value'     => 'datetime',
                  'label'     => Mage::helper('web')->__('Date Time'),
              ),
          ),
      ));


      $fieldset->addField('question', 'text', array(
          'label'     => Mage::helper('web')->__('Question'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'question',
      ));


      $fieldset->addField('answer', 'text', array(
          'label'     => Mage::helper('web')->__('Answer'),
//          'class'     => 'required-entry',
//          'required'  => true,
          'after_element_html' => '<small>Use semicolon between each answer</small>',
          'name'      => 'answer',
      ));



      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('web')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('web')->__('Enabled'),
              ),

              array(
                  'value'     => 0,
                  'label'     => Mage::helper('web')->__('Disabled'),
              ),
          ),
      ));
     

     
      if ( Mage::getSingleton('adminhtml/session')->getWebData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getWebData());
          Mage::getSingleton('adminhtml/session')->setWebData(null);
      } elseif ( Mage::registry('question_data') ) {
          $form->setValues(Mage::registry('question_data')->getData());
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
        return Mage::helper('web')->__('Question Information');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('web')->__('Question Information');
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