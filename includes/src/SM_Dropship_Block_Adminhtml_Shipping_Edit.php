<?php

class SM_Dropship_Block_Adminhtml_Shipping_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId    = 'rate_id';
        $this->_blockGroup  = 'smdropship';
        $this->_controller  = 'adminhtml_shipping';

        $this->_updateButton('save', 'label', Mage::helper('smdropship')->__('Save Shipping Config'));
        $this->_updateButton('delete', 'label', Mage::helper('smdropship')->__('Delete Shipping'));

        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
 		
    	return Mage::helper('smdropship')->__("Config Shipping Rate" . $this->getRate()->getTitle());
    }
    
    public function getRate(){
    	return Mage::registry('current_rate');
    }
}