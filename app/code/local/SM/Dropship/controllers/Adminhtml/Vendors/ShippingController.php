<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Configuration controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class SM_Dropship_Adminhtml_Vendors_ShippingController extends Mage_Adminhtml_Controller_Action {

    /**
     * Whether current section is allowed
     *
     * @var bool
     */
    protected $_isSectionAllowedFlag = true;

    /**
     * Controller predispatch method
     * Check if current section is found and is allowed
     *
     * @return Mage_Adminhtml_System_ConfigController
     */
    /*
      public function preDispatch()
      {
      parent::preDispatch();

      if ($this->getRequest()->getParam('section')) {
      $this->_isSectionAllowedFlag = $this->_isSectionAllowed($this->getRequest()->getParam('section'));
      }

      return $this;
      }
     */

    protected function _initAction() {

        $this->loadLayout()
                ->_setActiveMenu('smvendors/shipping');

        return $this;
    }

    /**
     * Index action
     *
     */
    public function indexAction() {
        if ($vendor = Mage::helper('smvendors')->getVendorLogin()) {
            $this->_redirect('*/*/edit', array('vendor_id' => $vendor->getVendorId()));
            return;
        }
        $this->_title($this->__('Shipping Manager'));
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('smdropship/adminhtml_shipping'));
        $this->renderLayout();
    }
    
    
    /**
     * AJAX grid action
     *
     */    
    public function gridAction() {
        $this->getResponse()
            ->setBody($this->getLayout()
            ->createBlock('smdropship/adminhtml_shipping')
            ->toHtml()
        );        
    }
    
    /**
     * AJAX grid action
     *
     */
    public function gridVendorAction() {
    	$rateId = $this->getRequest()->getParam('id');
    	$rate = Mage::getModel('smdropship/shipping_multiflatrate')->load($rateId);
    	Mage::register('current_rate', $rate);
    	$this->getResponse()
    	->setBody($this->getLayout()
    			->createBlock('smdropship/adminhtml_shipping_edit_tab_vendor')
    			->toHtml()
    	);
    }
    
    public function newAction(){
    	$this->_forward('edit');
    }
    
    /**
     * Edit configuration section
     *
     */
    public function editAction() {
        $this->_title($this->__('Vendors'))->_title($this->__('Shipping Methods'));

        $current = 'carriers'; //$this->getRequest()->getParam('section');
        $website = $this->getRequest()->getParam('website');
        $store = $this->getRequest()->getParam('store');
        $rateId = $this->getRequest()->getParam('id');
        $rate = Mage::getModel('smdropship/shipping_multiflatrate')->load($rateId);
        Mage::register('current_rate', $rate);
        $configFields = Mage::getSingleton('adminhtml/config');

        $sections = $configFields->getSections($current);
        $section = $sections->$current;
        $hasChildren = $configFields->hasChildren($section, $website, $store);
        if (!$hasChildren && $current) {
            $this->_redirect('*/*/', array('website' => $website, 'store' => $store, 'rate_id' => $rateId));
        }

        $this->loadLayout();

        $this->_setActiveMenu('smdropship/vendor_shipping');
        $this->getLayout()->getBlock('menu')->setAdditionalCacheKeyInfo(array($current));

        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('System'), Mage::helper('adminhtml')->__('System'), $this->getUrl('*/system'));


        $this->_addContent($this->getLayout()->createBlock('smdropship/adminhtml_shipping_edit'))	
         ->_addLeft($this->getLayout()->createBlock('smdropship/adminhtml_shipping_edit_tabs'));
        $this->getLayout()->getBlock('left')
                ->append($this->getLayout()->createBlock('adminhtml/store_switcher'));
        $this->_addJs($this->getLayout()
                        ->createBlock('adminhtml/template')
                        ->setTemplate('system/shipping/ups.phtml'));
        $this->_addJs($this->getLayout()
                        ->createBlock('adminhtml/template')
                        ->setTemplate('system/config/js.phtml'));
        $this->_addJs($this->getLayout()
                        ->createBlock('adminhtml/template')
                        ->setTemplate('system/shipping/applicable_country.phtml'));

        $this->renderLayout();
    }

    /**
     * Save configuration
     *
     */
    public function saveAction() {
        $session = Mage::getSingleton('adminhtml/session');
        /* @var $session Mage_Adminhtml_Model_Session */
        	if ($data = $this->getRequest()->getPost()) {
	           
	            $_model = Mage::getModel('smdropship/shipping_multiflatrate');
	            if($this->getRequest()->getParam('id')){
	            	$_model->setData($data)
	            	->setId($this->getRequest()->getParam('id'));
	            }
	            else{
	            	$_model->setData($data);
	            }
	            
	            
	            try {
	            	$_model->save();
	            	Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('smdropship')->__('Rate was successfully saved'));
	            	Mage::getSingleton('adminhtml/session')->setFormData(false);
	            	 
	            	if ($this->getRequest()->getParam('back')) {
	            		$this->_redirect('*/*/edit', array('id' => $_model->getId()));
	            		return;
	            	}
	            	$this->_redirect('*/*/');
	            	return;
	            } catch (Exception $e) {
	            	Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
	            	Mage::getSingleton('adminhtml/session')->setFormData($data);
	            	$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
	            	return;
	            }
        	}
		
        	Mage::getSingleton('adminhtml/session')->addError(Mage::helper('smdropship')->__('Unable to find rate to save'));
        	$this->_redirect('*/*/');
        	
    }
    
    public function deleteAction() {
    	if( $this->getRequest()->getParam('id') > 0 ) {
    		try {
    			$model = Mage::getModel('smdropship/shipping_multiflatrate');
    
    			$model->setId($this->getRequest()->getParam('id'))
    			->delete();
    
    			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Package was successfully deleted'));
    			$this->_redirect('*/*/');
    		} catch (Exception $e) {
    			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
    			$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
    		}
    	}
    	$this->_redirect('*/*/');
    }
    
    public function massDeleteAction() {
    	$IDList = $this->getRequest()->getParam('banner');
    	if(!is_array($IDList)) {
    		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select package(s)'));
    	} else {
    		try {
    			foreach ($IDList as $itemId) {
    				$_model = Mage::getModel('smdropship/shipping_multiflatrate')
    				->setIsMassDelete(true)->load($itemId);
    				$_model->delete();
    			}
    			Mage::getSingleton('adminhtml/session')->addSuccess(
    			Mage::helper('adminhtml')->__(
    			'Total of %d record(s) were successfully deleted', count($IDList)
    			)
    			);
    		} catch (Exception $e) {
    			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
    		}
    	}
    	$this->_redirect('*/*/index');
    }
    
    public function massStatusAction() {
    	$IDList = $this->getRequest()->getParam('banner');
    	if(!is_array($IDList)) {
    		Mage::getSingleton('adminhtml/session')->addError($this->__('Please select package(s)'));
    	} else {
    		try {
    			foreach ($IDList as $itemId) {
    				$_model = Mage::getSingleton('smdropship/shipping_multiflatrate')
    				->setIsMassStatus(true)
    				->load($itemId)
    				->setIsActive($this->getRequest()->getParam('status'))
    				->save();
    			}
    			$this->_getSession()->addSuccess(
    					$this->__('Total of %d record(s) were successfully updated', count($IDList))
    			);
    		} catch (Exception $e) {
    			$this->_getSession()->addError($e->getMessage());
    		}
    	}
    	$this->_redirect('*/*/index');
    }
}
