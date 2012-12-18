<?php

class Company_Web_Block_Adminhtml_Web_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('web_form', array('legend'=>Mage::helper('web')->__('Item information')));

      $model = Mage::registry('partner_edit');
     
      $fieldset->addField('company', 'text', array(
          'label'     => Mage::helper('web')->__('Company'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'company',
      ));


      $fieldset->addField('website', 'text', array(
          'label'     => Mage::helper('web')->__('Website'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'website',
      ));

      $image = '';
      if ($model->getData('image_url')) {
          $image = '<img src="'.$model->getData('image_url').'" />';
      }

      $fieldset->addField('filename', 'image', array(
          'label'     => Mage::helper('web')->__('Image'),
          'name'      => 'filename',
//          'after_element_html' => $image ,

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
                  'value'     => 2,
                  'label'     => Mage::helper('web')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('web')->__('Content'),
          'title'     => Mage::helper('web')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
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
        return Mage::helper('planet')->__('Partner Information');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('planet')->__('Partner Information');
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