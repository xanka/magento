<?php
/**
 * @category    SM
 * @package     SM_Vendors
 * @copyright   Copyright (c) 2012 SmartOSC (www.smartosc.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SM_Vendors_ListController extends Mage_Core_Controller_Front_Action
{
	/**
	 * Index action
	 */
	public function indexAction()
	{
		$this->loadLayout();
		
		if (($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs'))) {
			$breadcrumbs->addCrumb('home', array(
					'label' => Mage::helper('cms')->__('Home'),
					'title' => Mage::helper('cms')->__('Go to Home Page'),
					'link' => Mage::getBaseUrl()
			));
		
			$breadcrumbs->addCrumb('vendors_list', array(
					'label' => $this->__('Merchants'),
					'title' => $this->__('Merchants List'),
			));
		}
		
		$this->getLayout()->getBlock('head')->setTitle('List of Merchants');
		$this->renderLayout();
	}
}
