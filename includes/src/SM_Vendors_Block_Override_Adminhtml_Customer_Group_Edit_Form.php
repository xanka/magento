<?php

class SM_Vendors_Block_Override_Adminhtml_Customer_Group_Edit_Form extends Mage_Adminhtml_Block_Customer_Group_Edit_Form
{
    /**
     * Prepare form for render
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        
        $helper = Mage::helper('smvendors/form');
        /* @var $helper SM_Vendors_Helper_Form */
        $actionFieldset = $this->getForm()->getElement('base_fieldset');
        /* @var $actionFieldset Varien_Data_Form_Element_Fieldset */
        $vendorId = Mage::registry('current_group')->getData('vendor_id');
        
        if(($vendor = Mage::helper('smvendors')->getVendorLogin()) && !$vendorId) {
            $vendorId = $vendor->getId();
        }
        
        $helper->addHiddenField($actionFieldset, 'vendor_id', $vendorId ? $vendorId : 0);
        
        return $this;
    }
}
