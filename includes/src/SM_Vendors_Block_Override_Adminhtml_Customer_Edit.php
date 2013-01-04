<?php
class SM_Vendors_Block_Override_Adminhtml_Customer_Edit extends Mage_Adminhtml_Block_Customer_Edit
{
    public function __construct()
    {
        parent::__construct();
        
        if(Mage::helper('smvendors')->getVendorLogin()) {
            if ($this->getCustomerId() &&
                    Mage::getSingleton('admin/session')->isAllowed('smvendors/vendors_orders/actions/create')) {
                $this->_addButton('order', array(
                        'label' => Mage::helper('customer')->__('Create Order'),
                        'onclick' => 'setLocation(\'' . $this->getCreateVendorOrderUrl() . '\')',
                        'class' => 'add',
                ), 0);
            }
            
            $this->_removeButton('delete');
        }
    }

    public function getCreateVendorOrderUrl()
    {
        return $this->getUrl('*/vendors_order_create/start', array('customer_id' => $this->getCustomerId()));
    }
    
	protected function _getSaveAndContinueUrl()
    {
    	$action = '*/*/save';
    	if($vendor = Mage::helper('smvendors')->getVendorLogin()){
    		$action = '*/vendors_customer/saveCustomer'; 
    	}
        return $this->getUrl($action, array(
            '_current'  => true,
            'back'      => 'edit',
            'tab'       => '{{tab_id}}'
        ));
    }
}
