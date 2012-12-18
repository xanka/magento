<?php
class SM_Vendors_Block_Adminhtml_Page extends Mage_Adminhtml_Block_Widget_Grid_Container {
    public function __construct() {
        $this->_controller = 'adminhtml_page';
        $this->_blockGroup = 'smvendors';
        $this->_headerText = Mage::helper('smvendors')->__('Page Manager');
        parent::__construct();

        //$this->setTemplate('my_smvendors/banner.phtml');
    }

    protected function _prepareLayout() {
        $this->setChild('add_new_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label'     => Mage::helper('smvendors')->__('Add New Page'),
                'onclick'   => "setLocation('".$this->getUrl('*/*/add')."')",
                'class'   => 'add'
                ))
        );
        $this->setChild('grid', $this->getLayout()->createBlock('smvendors/adminhtml_page_grid', 'page.grid'));
        return parent::_prepareLayout();
    }

    public function getAddNewButtonHtml() {
        return $this->getChildHtml('add_new_button');
    }

    public function getGridHtml() {
        return $this->getChildHtml('grid');
    }
}