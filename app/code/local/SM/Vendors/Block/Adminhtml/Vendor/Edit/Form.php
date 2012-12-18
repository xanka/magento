<?php

class SM_Vendors_Block_Adminhtml_Vendor_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
        	'name'		=> 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method'    => 'post',
            'enctype'   => 'multipart/form-data'
        ));

        $customer = Mage::registry('current_customer');

        if ($customer->getId()) {
            $form->addField('entity_id', 'hidden', array(
                'name' => 'customer_id',
            ));
            $form->setValues($customer->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}