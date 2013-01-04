<?php
/**
 * @category    SM
 * @package     SM_Vendors
 * @copyright   Copyright (c) 2012 SmartOSC (www.smartosc.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SM_Vendors_ViewController extends SM_Vendors_Controller_Action
{
	/**
	 * Index action
	 */
	public function indexAction()
	{
		$this->loadLayout();
		$vendor = Mage::registry('current_vendor');
		
		$breadcrumbs = $this->_initBreadcrumbs();

		if ($breadcrumbs) {
			$breadcrumbs->addCrumb('vendor_'.$vendor->getVendorPrefix(), array(
					'label' => $vendor->getVendorName(),
					'title' => $vendor->getVendorName(),
			));
		}
		
		$this->_initLayoutMessages('catalog/session');
		$this->getLayout()->getBlock('head')->setTitle('Vendor');

		$this->renderLayout();
	}
}
