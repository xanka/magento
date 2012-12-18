<?php

class SM_Vendors_Block_Adminhtml_Vendor_Edit extends Mage_Adminhtml_Block_Customer_Edit {
	
	public function __construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_vendor';
		$this->_blockGroup = 'smvendors';
		
		$grandParent = $this->getGrandParent();
        call_user_func(array($grandParent, '__construct'));

        $this->_updateButton('save', 'label', Mage::helper('smvendors')->__('Save Vendor'));
        $this->_updateButton('delete', 'label', Mage::helper('smvendors')->__('Delete Vendor'));

        if (Mage::registry('current_customer')->isReadonly()) {
            $this->_removeButton('save');
            $this->_removeButton('reset');
        }

        if (!Mage::registry('current_customer')->isDeleteable()) {
            $this->_removeButton('delete');
        }
    }
	
	public function getHeaderText()
    {
        if (Mage::registry('current_customer')->getId()) {
            return $this->htmlEscape(Mage::registry('current_customer')->getName());
        }
        else {
            return Mage::helper('smvendors')->__('New Vendor');
        }
    }
	
	public function getGrandParent(){
		$grandParent = get_parent_class(get_parent_class($this));
		return $grandParent;
	}
}