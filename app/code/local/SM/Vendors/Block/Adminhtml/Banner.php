<?php
class SM_Vendors_Block_Adminhtml_Banner extends Mage_Adminhtml_Block_Widget_Grid_Container {
    public function __construct() {
        $this->_controller = 'adminhtml_banner';
        $this->_blockGroup = 'smvendors';
        $this->_headerText = Mage::helper('smvendors')->__('Banner Manager');
        parent::__construct();

        //$this->setTemplate('my_smvendors/banner.phtml');
    }

    protected function _prepareLayout() {
        $this->setChild('add_new_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label'     => Mage::helper('smvendors')->__('Add New Banner'),
                'onclick'   => "setLocation('".$this->getUrl('*/*/add')."')",
                'class'   => 'add'
                ))
        );
        $this->setChild('grid', $this->getLayout()->createBlock('smvendors/adminhtml_banner_grid', 'banner.grid'));
        return parent::_prepareLayout();
    }

    public function getAddNewButtonHtml() {
        return $this->getChildHtml('add_new_button');
    }

    public function getGridHtml() {
        return $this->getChildHtml('grid');
    }
}