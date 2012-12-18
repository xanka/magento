<?php
class SM_Reviews_ReviewController extends Mage_Core_Controller_Front_Action
{
	public function writeAction()
	{
		$vendorId =  $this->getRequest()->getParam('vendor_id', false);
		$customerId =  $this->getRequest()->getParam('customer_id', false);
		$orderId =  $this->getRequest()->getParam('order_id', false);
		
		if(!$vendorId || !$customerId || !$orderId) {
			$this->_redirect('/');
			return;
		}
		
		$hashKey = $this->getRequest()->getParam('hash', '');
		if($hashKey != Mage::helper('smreviews')->hashKey($customerId, $vendorId, $orderId)) {
			$this->_redirect('/');
			return;
		}
		
		$review = Mage::getModel('smreviews/reviews')->loadByCustomerVendorOrder($customerId, $vendorId, $orderId);
		if($review->getId()) {
			$this->_redirect('*/*/view', array('review_id' => $review->getId()));
			return;
		}
		
		$this->_loadLayout();
		$form = $this->getLayout()->createBlock('core/template', 'review.form', 
				array(
						'template' => 'sm/reviews/form.phtml'
						));
		
		$form->setVendorId($vendorId)
			 ->setCustomerId($customerId)
			 ->setOrderNumber($orderId);
// 		$order = Mage::getModel('sales/order')->load($orderId);
// 		if($order->getId()) {
// 			$form->setOrderNumber($order->getIncrementId());
// 		}
		$vendor = Mage::getModel('smvendors/vendor')->load($vendorId);
		if($vendor->getId()) {
			$form->setVendorName($vendor->getVendorName());
		}
		
		$form->setFormAction(Mage::getUrl('*/*/save'));
		
		$this->getLayout()->getBlock('content')->append($form);
		$this->renderLayout();
	}
	
	public function saveAction()
	{
		try {
			$session = Mage::getSingleton('core/session');
			
			$customerId = intval($this->getRequest()->getParam('customer_id'));
			$vendorId = $this->getRequest()->getParam('vendor_id');
			$orderId = $this->getRequest()->getParam('order_id');
			$comment = $this->getRequest()->getParam('comment');
			$rating = intval($this->getRequest()->getParam('rating'));
			
			$review = Mage::getModel('smreviews/reviews');
			$review->setComment(Mage::helper('core')->escapeHtml($comment))
					->setRating($rating)
					->setCustomerId($customerId)
					->setVendorId($vendorId)
					->setOrderId($orderId)
					->setStatus(SM_Reviews_Model_Reviews_Status::STATUS_DISABLED)
			;
			
			$customer = Mage::getModel('customer/customer')->load($customerId);
			if($customer->getId()) {
				$review->setCustomerName($customer->getName())
					->setCustomerEmail($customer->getEmail());
			}
			
			$review->save();
			$session->addSuccess(Mage::helper('smreviews')->__('Thank you for your review.'));
			$this->_redirect("*/*/view", array('review_id'=>$review->getId()));
		} catch (Exception $e) {
			$session->addException($e, Mage::helper('smreviews')->__('An error occurred while saving this review:') . ' '
					. $e->getMessage());
			$this->_redirect("*/*/write", array('customer_id'=>$customerId, 'vendor_id'=>$vendorId, 'order_id'=>$orderId));
		}
	}
	
	public function viewAction()
	{
		$reviewId = $this->getRequest()->getParam('review_id', false);
		if(!$reviewId) {
			$this->_redirect(Mage::getBaseUrl());
			return;
		}
		
		$review = Mage::getModel('smreviews/reviews')->load($reviewId);
		if(!$review->getId()) {
			$this->_redirect(Mage::getBaseUrl());
			return;
		}
		
		$this->_loadLayout();
		$form = $this->getLayout()->createBlock('core/template', 'review.view',
				array(
						'template' => 'sm/reviews/view.phtml'
				));
		
		$vendor = Mage::getModel('smvendors/vendor')->load($review->getVendorId());
		$form->setReview($review);
		$form->setVendor($vendor);

		$this->getLayout()->getBlock('content')->append($form);
		$this->renderLayout();
	}
	
	protected function _loadLayout()
	{
		$this->loadLayout(array('default', 'page_one_column'));
	}
}