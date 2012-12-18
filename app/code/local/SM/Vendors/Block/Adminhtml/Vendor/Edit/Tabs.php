<?php

class SM_Vendors_Block_Adminhtml_Vendor_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
	public function __construct()
    {
        parent::__construct();
        $this->setId('customer_info_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('smvendors')->__('Vendor Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('account', array(
            'label'     => Mage::helper('smvendors')->__('Account Information'),
            'content'   => $this->getLayout()->createBlock('adminhtml/customer_edit_tab_account')->initForm()->toHtml(),
            'active'    => Mage::registry('current_customer')->getId() ? false : true
        ));
		
		// implement logic later
		
		$this->addTab('vendor_info', array(
            'label'     => Mage::helper('smvendors')->__('Vendor Information'),
            'content'   => $this->getLayout()->createBlock('smvendors/adminhtml_vendor_edit_tab_form')->toHtml(),
        ));
		/*
		$this->addTab('vendor_page', array(
            'label'     => Mage::helper('smvendors')->__('Static page'),
            'content'   => $this->getLayout()->createBlock('smvendors/adminhtml_vendor_edit_tab_page')->toHtml(),
        ));
		*/
        $this->addTab('addresses', array(
            'label'     => Mage::helper('smvendors')->__('Addresses'),
            'content'   => $this->getLayout()->createBlock('adminhtml/customer_edit_tab_addresses')->initForm()->toHtml(),
        ));
        $this->_updateActiveTab();
        Varien_Profiler::stop('customer/tabs');
        return parent::_beforeToHtml();
    }
	
	protected function _updateActiveTab()
    {
        $tabId = $this->getRequest()->getParam('tab');
        if( $tabId ) {
            $tabId = preg_replace("#{$this->getId()}_#", '', $tabId);
            if($tabId) {
                $this->setActiveTab($tabId);
            }
        }
    }
}