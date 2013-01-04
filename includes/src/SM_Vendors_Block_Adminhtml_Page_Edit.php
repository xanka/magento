<?php

class SM_Vendors_Block_Adminhtml_Page_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId    = 'id';
        $this->_blockGroup  = 'smvendors';
        $this->_controller  = 'adminhtml_page';

        $this->_updateButton('save', 'label', Mage::helper('smvendors')->__('Save Page'));
        $this->_updateButton('delete', 'label', Mage::helper('smvendors')->__('Delete Page'));

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
        if (Mage::registry('page_data'))
        return Mage::helper('smvendors')->__("Edit Page '%s'", $this->htmlEscape(Mage::registry('page_data')->getTitle()));
        else
            return Mage::helper('smvendors')->__("New");
    }
}