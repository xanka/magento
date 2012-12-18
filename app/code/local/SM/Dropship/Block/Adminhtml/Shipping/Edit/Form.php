<?php
class SM_Dropship_Block_Adminhtml_Shipping_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
    protected function _prepareForm() {
    	$section = $this->getRequest()->getParam('section');
        $website = $this->getRequest()->getParam('website');
        $store   = $this->getRequest()->getParam('store');
        $vendorId = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form(
                array(
                        'id' => 'edit_form',
                        'action' => $this->getUrl('*/*/save', array('vendor_id' => $vendorId,'section'=>$section,'website'=>$website,'store'=>$store)),
                        'method' => 'post',
						'enctype'   => 'multipart/form-data'
                )
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}