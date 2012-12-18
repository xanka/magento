<?php

class SM_Vendors_ProductController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		$vendorId = $this->getRequest()->getParam('id');
		$vendor = Mage::getModel('smvendors/vendor')->load($vendorId);
		Mage::register('current_vendor',$vendor);
		if($vendor->getId()){
			$this->loadLayout();
			$this->_initLayoutMessages('catalog/session');
			$this->getLayout()->getBlock('head')->setTitle('Vendor');
			$this->renderLayout();
		}
		else{
			$this->_redirect('cms/noRoute');
		}
	}
}
