<?php
/**
 * @category    SM
 * @package     SM_Vendors
 * @copyright   Copyright (c) 2012 SmartOSC (www.smartosc.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class SM_Vendors_Controller_Action extends Mage_Core_Controller_Front_Action
{
    /**
	 * (non-PHPdoc)
	 * @see Mage_Core_Controller_Front_Action::preDispatch()
	 */
	public function preDispatch()
	{
		$vendor = null;
		
		$vendor = Mage::registry('current_vendor');
		
		if (!$vendor) {
			$vendorId = (int) $this->getRequest()->getParam('id');
			$vendor = Mage::getModel('smvendors/vendor')->load($vendorId);
			Mage::register('current_vendor', $vendor);
		}
			
		if (!$vendor || !$vendor->getId()) {
			$this->_redirect('cms/noRoute');
			return parent::preDispatch();
		}

		$categoryId = $this->getRequest()->getParam('cat');
		$category = Mage::getModel('catalog/category')->load($categoryId);
		

		if ($category->getId() && !Mage::registry('current_category')) {
			Mage::register('current_category', $category);
		}
		
		return parent::preDispatch();
	}
	
	/**
	 * 
	 * @return Mage_Page_Block_Html_Breadcrumbs
	 */
	protected function _initBreadcrumbs()
	{
		if (($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs'))) {
			$breadcrumbs->addCrumb('home', array(
					'label' => Mage::helper('cms')->__('Home'),
					'title' => Mage::helper('cms')->__('Go to Home Page'),
					'link' => Mage::getBaseUrl()
			));
			
			$breadcrumbs->addCrumb('vendors_list', array(
					'label' => $this->__('Merchant'),
					'title' => $this->__('Go to Merchants List'),
					'link' => Mage::helper('smvendors')->getVendorListUrl()
			));
		}
		
		return $breadcrumbs;
	}
}
