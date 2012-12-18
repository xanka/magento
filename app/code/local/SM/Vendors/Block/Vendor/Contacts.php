<?php
/**
 * @category    SM
 * @package     SM_Vendors
 * @copyright   Copyright (c) 2012 SmartOSC (www.smartosc.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SM_Vendors_Block_Vendor_Contacts extends Mage_Core_Block_Template
{
	public function __construct(){
		parent::__construct();
		$this->setTemplate('sm/vendors/contacts/form.phtml');
	}
	public function getFormAction(){
		if($this->getVendor()){
			return Mage::getUrl('vendor/contact',array('vendor_id'=>$this->getVendor()->getId()));
		}
		return '';
	}
	
	public function getVendor(){
		return Mage::registry('current_vendor');
	}
}