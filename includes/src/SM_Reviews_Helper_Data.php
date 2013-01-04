<?php
class SM_Reviews_Helper_Data extends Mage_Core_Helper_Abstract 
{
	const TEMPLATE_EMAIL_REQUEST_REVIEW_VENDOR = "smreviews_email_request_review_vendor";
	
	public function renderRating($rating, $fullImg = false, $blankImg = false, $fullImgAttr = "", $blankImgAttr = "")
	{
		if(!$fullImg) {
			$fullImg = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 
				'adminhtml/default/default/images/product_rating_full_star.gif';
		}
		if(!$blankImg) {
			$blankImg = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 
				'adminhtml/default/default/images/product_rating_blank_star.gif';
		}
		$rating = intval($rating);
		$html = "";
		for ($i=0; $i<$rating; $i++) {
			$html .= "<img src=\"{$fullImg}\" {$fullImgAttr} />";
		}
		for ($i=0; $i<5-$rating; $i++) {
			$html .= "<img src=\"{$blankImg}\" {$blankImgAttr} />";
		}
		return $html;
	}
	
	public function hashKey($customerId, $vendorId, $orderId)
	{
		$salt = "weRsmart";
		$key = "cid{$customerId}:vid{$vendorId}:oid{$orderId}:{$salt}";
		return sha1($key);
	}
	
	public function sendEmailReviewVendor(Mage_Sales_Model_Order $order, $vendorId)
	{
		if($orderId = $order->getIncrementId()) {
			$vendor = Mage::getModel('smvendors/vendor')->load($vendorId);
			$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
			if($customerId = $customer->getId()) {
				$emailTemplateVariables = array();
				$templateId = self::TEMPLATE_EMAIL_REQUEST_REVIEW_VENDOR;
				$sender = 'general';
				
				//Create an array of variables to assign to template
				$emailTemplateVariables['review_url'] = Mage::getUrl('vendor/review/write', 
						array(
								'vendor_id' => $vendorId,
								'customer_id' => $customerId,
								'order_id' => $orderId,
								'hash' => $this->hashKey($customerId, $vendorId, $orderId)
								));
				$emailTemplateVariables['customer'] = $customer;
				$emailTemplateVariables['vendor'] = $vendor;
				$emailTemplateVariables['order'] = $order;
				
				$translate = Mage::getSingleton('core/translate');
				/* @var $translate Mage_Core_Model_Translate */
				$translate->setTranslateInline(false);
				
				$email = Mage::getModel('core/email_template');
				$email->sendTransactional(
						$templateId,
						$sender,
						$customer->getEmail(),
						$customer->getName(),
						$emailTemplateVariables
				);
				$translate->setTranslateInline(true);	
			}
		}	
	}
	
	public function getReviewVendorUrl(Mage_Sales_Model_Order $order, $vendorId){
		$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
		if($orderId = $order->getIncrementId()) {
			if($customerId = $customer->getId()) {
				$url = Mage::getUrl('vendor/review/write',
				array(
					'vendor_id' => $vendorId,
					'customer_id' => $customerId,
					'order_id' => $orderId,
					'hash' => $this->hashKey($customerId, $vendorId, $orderId)
				));
				return $url;
			}
		}
		return '';
	}
}