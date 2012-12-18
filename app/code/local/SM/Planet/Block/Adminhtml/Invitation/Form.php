<?php

/*
 * 
 * 
 */

class SM_Planet_Block_Adminhtml_Invitation_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * Prepare form before rendering HTML
     *
     * @return SM_Planet_Block_Adminhtml_Invitation_Edit_Form
     */
    protected function _prepareForm() {


        $form = new Varien_Data_Form(array(
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/submit'),
                    'method' => 'post',
                        )
        );

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('planet')->__('Invite Customer'),
            'class' => 'fieldset'
                ));

        $fieldset->addField('email', 'text', array(
            'name' => 'email[]',
            'label' => Mage::helper('planet')->__('Send To'),
            'title' => Mage::helper('planet')->__('Email'),
            'class' => 'validate-email',
            'required' => true,
            'after_element_html' => '<a href="#" onclick="addInvitationEmail()">Add Email</a>',
        ));
        $form->setAction($this->getUrl('*/*/submit'));


        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

}