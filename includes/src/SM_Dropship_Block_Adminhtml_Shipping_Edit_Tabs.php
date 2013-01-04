<?php

class SM_Dropship_Block_Adminhtml_Shipping_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
	public function __construct()
    {
        parent::__construct();
        $this->setId('shipping_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('smdropship')->__('Rate Information'));
    }

    protected function _beforeToHtml()
    {
        $shippingMethods = $this->getAllShippingMethodSupportDropShipping();
        
        $this->addTab('shipping_rate', array(
        		'label'     => 'Rates',
        		'content'   => $this->getLayout()->createBlock('smdropship/adminhtml_shipping_edit_tab_form')->toHtml(),
        ));

        $this->addTab('related_section', array(
        		'label'     => Mage::helper('smdropship')->__('Vendors'),
        		'title'     => Mage::helper('smdropship')->__('Vendors'),
        		'content'   => $this->getLayout()->createBlock('smdropship/adminhtml_shipping_edit_tab_vendor')->toHtml(),
        ));
        
        $this->_updateActiveTab();
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