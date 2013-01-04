<?php

/*
 * 
 * 
 */

class SM_Planet_Block_Adminhtml_Customemail_Form extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * Prepare form before rendering HTML
     *
     * @return SM_Planet_Block_Adminhtml_Customemail_Edit_Form
     */
    protected function _prepareForm() {

        $params = $this->getRequest()->getParams();
        $form = new Varien_Data_Form(array(
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/submit'),
                'method' => 'post',
            )
        );

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('planet')->__('Fields Information'),
            'class' => 'fieldset-wide'
        ));

//        $fieldset->addField('name', 'text', array(
//            'name' => 'name',
//            'label' => Mage::helper('planet')->__('Name'),
//            'title' => Mage::helper('planet')->__('Name'),
//            'required' => true,
//            'value' => '$model->getName()',
//        ));
        $fieldset->addField('Order Id', 'text', array(
            'name' => 'order_id',
            'label' => Mage::helper('planet')->__('Order Id'),
            'container_id' => 'tr_help_text',
            // 'disabled' => true,
            'value' => $params['order_id'] ? $params['order_id'] : 0
        ));

        $fieldset->addField('customer_email', 'text', array(
            'name' => 'customer_email',
            'label' => Mage::helper('planet')->__('Customer Email'),
            'container_id' => 'tr_help_text',
            // 'disabled' => true,
            'value' => $params['customer'] ? $params['customer'] : ''
        ));


        $fieldset->addField('Decision', 'select', array(
            'name' => 'decision',
            'label' => Mage::helper('planet')->__('Decision'),
            'title' => Mage::helper('planet')->__('Decision'),
            'class' => 'required-entry',
            'required' => true,
            'values' => Mage::helper('planet')->getDecision()
        ));

        $fieldset->addField('reason', 'select', array(
            'name' => 'reason',
            'label' => Mage::helper('planet')->__('Reason'),
            'title' => Mage::helper('planet')->__('Reason'),
            'class' => 'required-entry',
            'required' => true,
            'values' => Mage::helper('planet')->getRejectReason()
        ));

        $fieldset->addField('Note', 'textarea', array(
            'name' => 'help_text',
            'label' => Mage::helper('planet')->__('Note'),
            'container_id' => 'tr_help_text',
            'value' => ''
        ));





        $form->setAction($this->getUrl('*/*/submit'));


        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

}