<?php

class SM_Vendors_Block_Adminhtml_Banner_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('smvendors_banner_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('smvendors')->__('Banner Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general_section', array(
            'label'     => Mage::helper('smvendors')->__('Banner Info'),
            'title'     => Mage::helper('smvendors')->__('Banner Info'),
            'content'   => $this->getLayout()->createBlock('smvendors/adminhtml_banner_edit_tab_main')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}