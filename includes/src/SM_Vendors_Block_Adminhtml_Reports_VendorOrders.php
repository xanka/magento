<?php

class SM_Vendors_Block_Adminhtml_Reports_VendorOrders extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
    	
    	$this->_blockGroup = 'smvendors';
        $this->_controller = 'adminhtml_reports_vendorOrders';
        $this->_headerText = Mage::helper('smvendors')->__('Vendor Orders Report');
        parent::__construct();
        $this->setTemplate('sm/vendors/reports/vendor/orders/grid/container.phtml');
        $this->_removeButton('add');
        $this->addButton('filter_form_submit', array(
            'label'     => Mage::helper('smvendors')->__('Show Report'),
            'onclick'   => 'filterFormSubmit()'
        ));
    }

    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/vendororders', array('_current' => true));
    }
}
