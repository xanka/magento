<?php
/**
 * @category    Fishpig
 * @package     Fishpig_WpCustomerSynch
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Addon_CS_Model_Observer
{
	/**
	 * This observer runs each time a customer logs in to Magento
	 * First, Magento checks whether the customer exists
	 * if so, it pushes it to WP
	 * If not, it checks in WP for a user
	 * If exists, pulls to Magento
	 *
	 * @param Varien_Event_Observer $observer
	 * @return bool
	 */
	public function customerAccountLoginPostObserver(Varien_Event_Observer $observer)
	{
		if (!$this->isCustomerSynchronisationEnabled()) {
			return false;
		}

		try {
			$user = false;
			
			if ($login = Mage::app()->getRequest()->getPost('login')) {
				$customer = Mage::getModel('customer/customer')
					->setWebsiteId(Mage::app()->getStore()->getWebsite()->getId())
					->loadByEmail($login['username'])
					->setPassword($login['password']);
				
				if ($customer->getId()) {
					$this->synchroniseCustomer($customer);
					
					$this->loginToWordPress($customer);
				}
				else {
					$user = Mage::getModel('wordpress/user')->loadByEmail($login['username'])
						->setMagentoPassword($login['password']);
					
					if ($user->getId()) {
						$this->synchroniseUser($user);
					}
					
					$this->loginToWordPress($customer, $user);
				}
			}
		}
		catch (Exception $e) {
			Mage::helper('wordpress')->log($e);
		}
		
		return true;
	}
	
	/**
	 * This observer runs each time a customer model is loaded
	 * It takes a copy of any data to be synchronised and stores it in orig_data
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function customerLoadAfterObserver(Varien_Event_Observer $observer)
	{
		if (!$this->isCustomerSynchronisationEnabled()) {
			return false;
		}
		
		$customer = $observer->getEvent()->getCustomer();

		$customer->setCsData(new Varien_Object(array(
			'firstname' => $customer->getFirstname(),
			'lastname' => $customer->getLastname(),
			'email' => $customer->getEmail(),
			'password_hash' => $customer->getPasswordHash(),
		)));
		
		$user = Mage::getModel('wordpress/user')->loadByEmail($customer->getEmail());
		
		if ($user->getId()) {
			$customer->setWordpressUser($user);
		}
	}
	
	/**
	 * This observer runs each time a customer model is saved
	 *
	 * @param Varien_Event_Observer $observer
	 * @return bool
	 */
	public function customerSaveAfterObserver(Varien_Event_Observer $observer)
	{
		if (!$this->isCustomerSynchronisationEnabled()) {
			return false;
		}

		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			$customer = Mage::getSingleton('customer/session')->getCustomer();
		}
		else {
			$customer = $observer->getEvent()->getCustomer();
		}
		
		$this->synchroniseCustomer($customer);

		return true;
	}
	
	/**
	 * This observer is triggered after an order is placed
	 *
	 * @param Varien_Event_Observer $observer
	 * @return bool
	 */
	public function onepageSaveOrderAfterObserver(Varien_Event_Observer $observer)
	{
		if (!$this->isCustomerSynchronisationEnabled()) {
			return false;
		}

		try {
			$quote = $observer->getEvent()->getQuote();
			$customer = $quote->getCustomer();
			$customer->setPassword($customer->decryptPassword($quote->getPasswordHash()));

			return $this->synchroniseCustomer($customer);
		}
		catch (Exception $e) {
			Mage::helper('wordpress')->log($e);
			return false;
		}
	}
	
	/**
	 * Synchronise a Magento customer with WordPress
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @return bool
	 */
	public function synchroniseCustomer(Mage_Customer_Model_Customer $customer)
	{
		$email = $customer->getEmail();
		
		if ($customer->getCsData() && $customer->getCsData()->getEmail()) {
			$email = $customer->getCsData()->getEmail();
		}

		$user = Mage::getModel('wordpress/user')->loadByEmail($email);
		
		if (!$user->getId() && !$customer->getPassword()) {
			return false;
		}
		
		$user->setUserEmail($customer->getEmail())
			->setUserRegistered($customer->getCreatedAt())
			->setUserNicename($customer->getName())
			->setDisplayName($customer->getFirstname())
			->setFirstName($customer->getFirstname())
			->setLastName($customer->getLastname())
			->setNickname($customer->getFirstname());
		
		if (!$user->getUserLogin() || strpos($user->getUserLogin(), '@') !== false) {
			$user->setUserLogin($customer->getEmail());
		}

		try {
			if ($customer->hasPassword()) {
				if ($customer->validatePassword($customer->getPassword())) {
					$wpHash = $this->hashPasswordForWordPress($customer->getPassword());

					if ($this->isValidWordPressPassword($customer->getPassword(), $wpHash)) {
						$user->setUserPass($wpHash);
					}
				}
			}

			$user->save();
			
			$customer->setWordpressUser($user);
		}
		catch (Exception $e) {
			Mage::helper('wordpress')->log($e);
		}
	}
	
	/**
	 * Push a WordPress user model to Magento
	 *
	 * @param Fishpig_Wordpress_Model_User $user
	 * @return bool
	 */
	public function synchroniseUser(Fishpig_Wordpress_Model_User $user)
	{
		$customer = Mage::getModel('customer/customer')
			->setWebsiteId(Mage::app()->getStore()->getWebsite()->getId())
			->loadByEmail($user->getUserEmail());
			
		if (!$customer->getId() && !$user->getMagentoPassword()) {
			return false;
		}
		
		$customer->setEmail($user->getUserEmail())
			->setFirstname($user->getFirstName())
			->setLastname($user->getLastName())
			->setStoreId(Mage::app()->getStore()->getId());
			
		if ($user->getMagentoPassword()) {
			$customer->setPassword($user->getMagentoPassword());
		}
		
		try {
			$customer->save();
			$customer->setWordpressUser($user);
		}
		catch (Exception $e) {
			Mage::helper('wordpress')->log($e);
		}
		
		return false;
	}
	
	/**
	 * Test whether can open the PhPassword file
	 *
	 * @return bool
	 */
	public function canOpenPhPasswordFile()
	{
		try {
			if ($this->_requirePhPassClass()) {
				return true;
			}
		}
		catch (Exception $e) {
			if (Mage::getDesign()->getArea() == 'adminhtml') {
				Mage::getSingleton('adminhtml/session')->addError('Customer Synch Error: ' . $e->getMessage());
			}
			
			Mage::helper('wordpress')->log("There was an error including your PhPassword file (see error in entry below)");
			Mage::helper('wordpress')->log($e);
		}
		
		return false;
	}
	
	/**
	 * Force inclusion of WordPress Password class file
	 */
	protected function _requirePhPassClass()
	{
		if (is_null(Mage::registry('_wordpress_require_phpass'))) {
			$classFile = Mage::helper('wordpress')->getWordPressPath() . 'wp-includes/class-phpass.php';

			if (file_exists($classFile) && is_readable($classFile)) {
				require_once($classFile);
				Mage::register('_wordpress_require_phpass', true, true);
			}
			else {
				Mage::register('_wordpress_require_phpass', false, true);
				Mage::helper('wordpress')->log(Mage::helper('wordpress')->__('Error including password file (%s)', $classFile));
			}
		}
		
		return Mage::registry('_wordpress_require_phpass');
	}

	/**
	 * Returns true if the password can be hashed to equal $hash
	 *
	 * @oaram string $password
	 * @param string(hash) $hash
	 * @return bool
	 */
	public function isValidWordPressPassword($password, $hash)
	{
		$this->_requirePhPassClass();
						
		$wpHasher = new PasswordHash(8, TRUE);

		return $wpHasher->CheckPassword($password, $hash) ? true : $hash == md5($password);
	}
	
	/**
	 * Convert a string to a valid WordPress password hash
	 *
	 * @param string $password
	 * @return string
	 */
	public function hashPasswordForWordPress($password)
	{
		$this->_requirePhPassClass();
		
		if (class_exists('PasswordHash')) {
			$wpHasher = new PasswordHash(8, TRUE);
		
			return $wpHasher->HashPassword($password);
		}
		
		throw new Exception('Cannot find class PasswordHash');
	}
	
	/**
	 * Determine whether synchronisation can be ran
	 *
	 * @return bool
	 */
	public function isCustomerSynchronisationEnabled()
	{
		if ($this->isEnabled()) {
			if ($this->canOpenPhPasswordFile()) {
				return true;
			}
		}

		return false;
	}
	
	/**
	 * Determine whether customer synch is enabled in the admin
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return Mage::getStoreConfigFlag('wordpress_extend/customer_synchronisation/enabled');
	}
	
	/**
	 * Login to WordPress
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @param Fishpig_Wordpress_Model_User $user = null
	 * @return bool
	 */
	public function loginToWordPress(Mage_Customer_Model_Customer $customer, Fishpig_Wordpress_Model_User $user = null)
	{
		if (is_null($user)) {
			$user = $customer->getWordpressUser();
			
			if (!$user) {
				return false;
			}
		}
		
		if (!$customer->getPassword()) {
			return false;
		}
		
		try {
			return Mage::helper('wordpress/system')->loginToWordPress($user->getUserLogin(), $customer->getPassword());
		}
		catch (Exception $e) {
			Mage::helper('wordpress')->log($e);
			
			return false;
		}
	}
}
