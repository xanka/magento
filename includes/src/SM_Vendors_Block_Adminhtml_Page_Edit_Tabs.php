<?php

class SM_Vendors_Block_Adminhtml_Page_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('smvendors_page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('smvendors')->__('Page Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general_section', array(
            'label'     => Mage::helper('smvendors')->__('Page Info'),
            'title'     => Mage::helper('smvendors')->__('Page Info'),
            'content'   => $this->getLayout()->createBlock('smvendors/adminhtml_page_edit_tab_main')->toHtml(),
        ))->addTab('content_section', array(
            'label'     => Mage::helper('smvendors')->__('Content'),
            'title'     => Mage::helper('smvendors')->__('Content'),
            'content'   => $this->getLayout()->createBlock('smvendors/adminhtml_page_edit_tab_content')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}