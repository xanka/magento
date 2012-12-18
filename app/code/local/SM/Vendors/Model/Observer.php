<?php 

class SM_Vendors_Model_Observer
{
	public function hookAdminCustomerPrepareSave($observer){
		$customer = $observer->getCustomer();
		$request = $observer->getRequest();
		$vendorData = $request->getPost('vendor');
		$postData = $request->getPost('account');
		if(!empty($postData['password'])){
			$customer->setData('user_password',$postData['password']);
			$customer->setPassword($postData['password']);
		}
		elseif(!empty($postData['new_password'])){
			$customer->setData('user_password',$postData['new_password']);
			$customer->setPassword($postData['new_password']);
		}
		if(!empty($vendorData)){
			$customer->addData($vendorData);
		}
		if(isset($vendorData['vendor_shipping_methods'])){
			$vendorShippingMethods = $vendorData['vendor_shipping_methods'];
			$customer->setData('vendor_shipping_methods',implode(',',$vendorShippingMethods));
		}
		
		if(isset($vendorData['vendor_shipping_rate_free'])){
			$vendorShippingMethods = $vendorData['vendor_shipping_rate_free'];
			$customer->setData('vendor_shipping_rate_free',$vendorData['vendor_shipping_rate_free']);
		}
		
		if(isset($vendorData['vendor_shipping_rate_nofree'])){
			$vendorShippingMethods = $vendorData['vendor_shipping_rate_nofree'];
			$customer->setData('vendor_shipping_rate_nofree',implode(',',$vendorShippingMethods));
		}
		
		if(isset($vendorData['vendor_shipping_methods'])){
			$vendorVeganSociety = $vendorData['is_vendor_vegan_society'];
			$customer->setData('is_vendor_vegan_society',$vendorVeganSociety);
		}
		else{
			$customer->setData('is_vendor_vegan_society',0);
		}
		
		if(isset($vendorData['vendor_is_vendor_vegan_society_approve'])){
			$vendorVeganSocietyApprove = $vendorData['vendor_is_vendor_vegan_society_approve'];
			$customer->setData('vendor_is_vendor_vegan_society_approve',$vendorVeganSocietyApprove);
		}
		else{
			$customer->setData('vendor_is_vendor_vegan_society_approve',0);
		}
		
		$imagedata = array();
        if (!empty($_FILES['vendor_logo']['name'])) {
            try {
                $ext = substr($_FILES['vendor_logo']['name'], strrpos($_FILES['vendor_logo']['name'], '.') + 1);
                $fname = 'File-' . time() . '.' . $ext;
                $uploader = new Varien_File_Uploader('vendor_logo');
                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png')); // or pdf or anything

                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);

                $path = Mage::getBaseDir('media').DS.'vendor'.DS.'banners';

                $uploader->save($path, $fname);

                $imagedata['vendor_logo'] = 'vendor/banners/'.$fname;
				$customer->setData('vendor_logo',$imagedata['vendor_logo']);
            } catch (Exception $e) {
               Mage::logException($e);
            }
        }
		
		if (empty($imagedata['vendor_logo'])) {
			$vendorLogo = $request->getPost('vendor_logo');
            if (isset($vendorLogo['delete']) && $vendorLogo['delete'] == 1) {
				if ($vendorLogo['value'] != '') {
					$_helper = Mage::helper('smvendors');
					$file = Mage::getBaseDir('media').DS.$_helper->updateDirSepereator($vendorLogo['value']);
					try {
						$io = new Varien_Io_File();
						$result = $io->rmdir($file, true);
					} catch (Exception $e) {
					    Mage::logException($e);
					}
				}
				$imagedata['vendor_logo']='';
				$customer->setData('vendor_logo',$imagedata['vendor_logo']);
			}
		}
		
	}
	
	public function hookOrderPlaceAfter($observer){
		$order = $observer->getOrder();
		/* @var $order Mage_Sales_Model_Order */
// 		$quote = $observer->getQuote();
// 		$quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
		/* @var $quote Mage_Sales_Model_Quote */
		
		if($customerId = $order->getCustomerId()){
			$vendors = array();
			foreach($order->getAllItems() as $item){
				$vendorId = $item->getVendorId();
				$vendors[$vendorId] = $vendorId;

			}

			foreach($vendors as $vendor){
                if (is_null($vendor)) continue;
			    $resource = Mage::getModel('core/resource');
			    /* @var $resource Mage_Core_Model_Resource */
			    $resource->getConnection('core_write')
			             ->insertOnDuplicate(Mage::getModel('core/resource')->getTableName('smvendors/vendor_customer'), 
			                     array('vendor_id' => $vendor, 'customer_id' => $customerId));
			}
		}
		
	    Mage::getSingleton('smvendors/order')->splitOrder($order);
	    
		return $this;
	}

	public function hookCartProductAddAfter($observer){
        $quoteItem = $observer->getEvent()->getQuoteItem();
        /* @var $quoteItem Mage_Sales_Model_Quote_Item */
        if(!$quoteItem || !$quoteItem->getProductId()) {
            Mage::log("[smvendors][hookCartProductAddAfter] Invalid quote item.");
            return $this;
        }

        $product = $observer->getEvent()->getProduct();
        if(!$product) {
            $product = Mage::getModel('catalog/product')->load($quoteItem->getProductId());
        }

        if(!$product->getId()) {
            Mage::log("[smvendors][hookCartProductAddAfter] Product not exist. Item id={$quoteItem->getId()}");
            return $this;
        }

        $quoteItem->setVendorId($product->getData('sm_product_vendor_id'));
        if($quoteParentItem = $quoteItem->getParentItem()) {
            $quoteParentItem->setVendorId($quoteItem->getVendorId());
        }
        //$quoteItem->save();
        return $this;
	}
	
	
	public function hookCartProductUpdateItemAfter($observer){
		$shippingMethod = Mage::app()->getRequest()->getPost('shipping_method');
		if(!empty($shippingMethod)){
			Mage::getSingleton('checkout/cart')
				->getQuote()
				->getShippingAddress()
				->setShippingMethod($shippingMethod)
				->save();
		}
		return $this;
	}
	
	/**
     * Auto add default shipping address
     *
     * @return SM_Vendors_Model_Observer
     */
	
	public function hookCheckoutCartAddPreDispatch($observer){
        return;
		//die('dfsdf');
		if(!$this->_getQuote()->getShippingAddress()->getCountryId()){
			$store = Mage::app()->getStore()->getId();
			$customer = Mage::getSingleton('customer/session')->getCustomer();
			if($customer->getId()){
				
				$shippingAddress = $customer->getDefaultShippingAddress();
				$billingAddress = $customer->getDefaultBillingAddress();
				if(!empty($shippingAddress)){
					$this->_getQuote()->setShippingAddress($shippingAddress);
				}
				if(!empty($billingAddress)){
					$this->_getQuote()->setBillingAddress($billingAddress);
				}
				$this->_getQuote()
						->collectTotals()
						->setCollectShippingRates(true)
						->save();
				$this->_getQuote()->setDataChanges(true);
			}
			else{
				$this->_getQuote()->getShippingAddress()
					->setCountryId(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $store))
					->setRegionId(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_REGION_ID, $store))
					->setCity(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_CITY, $store))
					->setPostcode(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ZIP, $store))
					->setCollectShippingRates(true);
				$this->_getQuote()->save();
				$this->_getQuote()->setDataChanges(true);
			}
		}
		return $this;
	}
	
	public function hookCustomerGroupBeforeSave($observer)
	{
	    $customerGroup = $observer->getEvent()->getDataObject();
	    $customerGroup->setVendorId(Mage::app()->getRequest()->getParam('vendor_id', 0));
	}
	
	/**
     * Change vendor password when customer change password
     *
     * @return SM_Vendors_Model_Observer
     */
	public function hookCustomerRegisterSuccess($observer) {
		$customer = $observer->getEvent()->getCustomer();

		$password = $customer->getPassword();
		if (!empty($password)) {
			$addresses = $customer->getAddresses();
			$vendorName = '';
			
			if (is_array($addresses) && sizeof($addresses)) {
				$address = reset($addresses); 
				$vendorName = $address->getCompany();
			}
			
//			if (!$vendorName) {
//				$vendorName = $customer->getFirstname() . ' ' . $customer->getLastname();
//			}
			
//			$customer->setData('vendor_name' , $vendorName);
			$customer->setData('vendor_status' , 0);
			$customer->setData('user_password', $password);
			$customer->save();
		}

        if ($customer->getData('customer_type') == 'vendor') {
            $helper = Mage::helper('smvendors/email')->sendAdminWhenVendorRegister($customer);
        }
		return $this;
	}
	
	/**
     * Change vendor password when customer change password
     *
     * @return SM_Vendors_Model_Observer
     */
	public function hookCustomerAccountEditPostPreDispatch($observer){
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		$request = Mage::app()->getRequest();
		$postData = $request->getPost();
		if(!empty($postData['password']) && !empty($postData['confirmation']) && $postData['confirmation'] == $postData['password']){
			$customer->setData('user_password',$postData['password']);
			$customer->save();
		}
		return $this;
	}
	
	/**
	 * Set vendor id for order history item when save
	 */
	public function hookSalesOrderStatusHistorySaveBefore($observer)
	{
	    if($vendor = Mage::helper('smvendors')->getVendorLogin()) {
	        $historyItem = $observer->getEvent()->getDataObject();
	        if($historyItem->getId() === null) {
	            $historyItem->setVendorId($vendor->getId());
	        }
	    }
	}

	
	/**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current active quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    public function sendingreviewemail() {
        if(Mage::getConfig()->getModuleConfig('SM_Reviews')->is('active', 'true')){
            $orders = Mage::getModel('smvendors/order')->getCollection();
            $date = date('m/d/Y h:i:s', time());

            foreach($orders as $order) {
                if (!is_null($order->getData('hassendreview'))) continue;
                $d1 = new DateTime($date);
                $d2 = new DateTime($order->getData('created_at'));
                date_add($d2, date_interval_create_from_date_string('7 days'));

                if ($d1 >= $d2) {
                    $order->setData('hassendreview',1); // Mark as send review already
                    $order->save();
                    // start sending email request review
                    Mage::helper('smreviews')->sendEmailReviewVendor($order, $order->getData('vendor_id'));
                }
            }
        }
    }
	
}
