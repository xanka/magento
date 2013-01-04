<?php


class SM_Planet_Block_Override_Customer_Account_Navigation extends Mage_Customer_Block_Account_Navigation
{
	protected function _prepareLayout(){
		if(Mage::getSingleton('customer/session')->isLoggedIn()){
			$customer = Mage::getSingleton('customer/session')->getCustomer();
			if($customer->getCustomerType()  != 'vendor'){
//				$this->addLink('become_to_vendor', 'vendor/account/create', $this->__('Become To Vendor'));
				//$block = $this->getLayout()->createBlock('cms/block')->setBlockId('vendor_signup');
				//$this->getLayout()->getBlock('left')->append($block,'vendor_signup');
			}
			else{
				/*
				if($customer->getVendorStatus() == 1){
					$user = Mage::getModel('admin/user')->load($customer->getUserId());
					
					$this->addLink('login_admin_panel', 'adminhtml/index/index', $this->__('Login Admin'),array('auto_login'=>1,'hash_key'=>Mage::helper('planet')->getHashKey($user->getPassword()),'user_id'=>$customer->getUserId()));
				}
				else{
					$this->addLink('login_admin_panel', 'vendor-message', $this->__('Login Admin'));
				}
				*/
			}
			
		}
		return parent::_prepareLayout();
	}
	
}
