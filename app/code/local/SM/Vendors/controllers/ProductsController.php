<?php

class SM_Vendors_ProductsController extends SM_Vendors_Controller_Action
{
	public function indexAction()
	{
		$vendor = Mage::registry('current_vendor');
		$currentCategory = Mage::registry('current_category');
        Mage::getSingleton('core/session')->setData('current_vendor',$vendor);

        $this->loadLayout();
		
		$breadcrumbs = $this->_initBreadcrumbs();
		
		if ($breadcrumbs) {
			if ($currentCategory) {
				$breadcrumbs->addCrumb('vendor_'.$vendor->getVendorPrefix(), array(
						'label' => $vendor->getVendorName(),
						'title' => $vendor->getVendorName(),
						'link' => $vendor->getVendorUrl(),
				));
				
				$path  = Mage::helper('smvendors/catalog')->getBreadcrumbPath($vendor);
				foreach ($path as $name => $breadcrumb) {
					$breadcrumbs->addCrumb($name, $breadcrumb);
				}
			} else {
				$breadcrumbs->addCrumb('vendor_'.$vendor->getVendorPrefix(), array(
						'label' => $vendor->getVendorName(),
						'title' => $vendor->getVendorName(),
				));
			}
		}
		
		$this->_initLayoutMessages('catalog/session');
		$this->getLayout()->getBlock('head')->setTitle("{$vendor->getVendorName()}' Products");
		$this->renderLayout();
	}
}
