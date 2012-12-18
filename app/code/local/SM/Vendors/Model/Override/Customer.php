<?php 
/**
 * @category    SM
 * @package     SM_Vendors
 * @copyright   Copyright (c) 2012 SmartOSC (www.smartosc.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SM_Vendors_Model_Override_Customer extends Mage_Customer_Model_Customer
{
	/**
	 *
	 * @var string
	 */
	const CUSTOMER_TYPE_VENDOR = 'vendor';
	/**
	 *
	 * @var string
	 */
	const CUSTOMER_TYPE_BUYER = 'buyer';
	
	const XML_PATH_VENDOR_REGISTER_EMAIL_TEMPLATE 	= 'customer/create_account/vendor_email_template';
	const XML_PATH_VENDOR_CONFIRM_EMAIL_TEMPLATE    = 'customer/create_account/vendor_email_confirmation_template';
	const XML_PATH_VENDOR_CONFIRMED_EMAIL_TEMPLATE  = 'customer/create_account/vendor_email_confirmed_template';
	const XML_PATH_VENDOR_APPROVED_EMAIL_TEMPLATE  = 'customer/create_account/vendor_email_approved_template';
	
	/**
	 * 
	 * @var SM_Vendors_Model_Vendor
	 */
	protected $vendorModel;
	
	/**
	 * 
	 * @var array 
	 */
	protected $_canSeePrice = null;
	
	/**
	 * 
	 * @var array
	 */
	protected $_groups = null;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->vendorModel = Mage::getModel('smvendors/vendor');
	}

	/**
	 * 
	 * @return SM_Vendors_Model_Vendor
	 */
	public function getVendorModel()
	{
		return $this->vendorModel;
	}

	/**
	 * (non-PHPdoc)
	 * @see Mage_Core_Model_Abstract::_afterLoad()
	 */
	public function _afterLoad()
	{
		if ($this->getData('customer_type') == 'vendor') {
			$this->vendorModel = Mage::getModel('smvendors/vendor')->loadByCustomerId($this->getId());
			if ($this->vendorModel->getCustomerId() == $this->getId()) {
				$this->getVendorData();
			}
		} else {
			$this->vendorModel = Mage::getModel('smvendors/vendor');
		}
		
		return parent::_afterLoad();
	}
	
	/**
	 * 
	 * @return SM_Vendors_Model_Override_Customer
	 */
	public function getVendorData()
	{
		$this->addData($this->vendorModel->getData());
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Core_Model_Abstract::_afterSave()
	 */
	public function _afterSave()
	{
		if ($this->getId()) {
			if ($this->getData('customer_type') == 'vendor') {
				if (!empty($this->vendorModel)) {
					$this->vendorModel->addData($this->getData());
					$this->vendorModel->setCustomerId($this->getId());
					// create account for vendor
						
					$vendorRole = Mage::helper('smvendors')->getVendorRole();
					$data = array(
							'username' => $this->getEmail(),
							'email' => $this->getEmail(),
							'firstname' => $this->getFirstname(),
							'lastname' => $this->getLastname(),
							'is_active' =>$this->vendorModel->getVendorStatus(),
							'roles' => array($vendorRole)
					);
					
					$newPassword = $this->getData('user_password');
					
					if (!empty($newPassword)) {
						$data['password'] = $newPassword;
						$data['password_confirmation'] = $newPassword;
					}
					
					if ($data) {
						$id = $this->vendorModel->getUserId();
						$model = Mage::getModel('admin/user')->load($id);
						
						if(!$model->getIsActive() && $data['is_active']) {
						    $this->sendNewAccountEmail('approved','',Mage::helper('smvendors')->getDefaultStoreId());
						}
						$model->addData($data);
							
						$result = $model->validate();
							
						if (is_array($result)) {
							Mage::getSingleton('adminhtml/session')->setUserData($data);
							foreach ($result as $message) {
								Mage::getSingleton('adminhtml/session')->addError($message);
							}
						}

						try {
							$model->save();
							
							if ($uRoles = $data['roles']) {
								if (1 == sizeof($uRoles)) {
									$model->setRoleIds($uRoles)
									->setRoleUserId($model->getUserId())
									->saveRelations();
								} elseif (sizeof($uRoles) > 1) {
									$rs = array();
									$rs[0] = $uRoles[0];
									$model->setRoleIds( $rs )->setRoleUserId( $model->getUserId() )->saveRelations();
								}
							}
							
							$this->vendorModel->setUserId($model->getUserId());
							
							$passwordHash = trim($this->getPasswordHash());
							
							if(!empty($passwordHash)){
								//print_r($this->getData());die;
								$resource = Mage::getSingleton('core/resource');
								$writeConnection = $resource->getConnection('core_write');
								$tableUser = $resource->getTableName('admin/user');
								$query = "UPDATE {$tableUser} SET password = '{$passwordHash}' WHERE user_id = ". (int)$model->getUserId();
								$writeConnection->query($query);
							}
							
							Mage::getSingleton('adminhtml/session')->setUserData(false);
						} catch (Mage_Core_Exception $e) {
							Mage::getSingleton('adminhtml/session')->addError($this->__('Customer is saved, but this error happened: ' . $e->getMessage()));
							Mage::logException($e);
						}
					}
					
					$this->vendorModel->save();
				}
			}
		}
		
		return parent::_afterSave();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Core_Model_Abstract::_afterDelete()
	 */
	public function _afterDelete(){
		$result = parent::_afterDelete();
		if($this->getVendorModel()->getId()){
			$this->getVendorModel()->delete();
		}
		return $result;
	}
	
	/**
	 * 
	 * @return array
	 */
	public function getGroups()
	{
		if (is_null($this->_groups)) {
			$groupIds = (string) $this->getVendorCustomerGroup();
			$groupIds = explode(',', $groupIds);
			
			foreach ($groupIds as $i => $groupId) {
				$groupIds[$i] = (int) $groupId;
			}
			
			$resource = Mage::getSingleton('core/resource');
			$read = $resource->getConnection('core_read');
			
			$select = $read
				->select()
				->from($resource->getTableName('customer/customer_group'))
				->where('customer_group_id IN (?)', $groupIds);

			$rows = $read->fetchAssoc($select);
			
			foreach ($rows as $row) {
				$this->_groups[] = new Varien_Object($row);
			}
		}
		
		return $this->_groups;
	}
	
	/**
	 * 
	 * @param int $vendorId
	 * @return boolean
	 */
	public function canSeePrice($vendorId)
	{
		$vendorId = (int) $vendorId;
		
		if (is_null($this->_canSeePrice)) {
			$this->_canSeePrice = array();
			$groups = $this->getGroups();
			foreach ($groups as $group) {
			    $this->_canSeePrice[] = (int) $group->getVendorId();  
			}
		}
		
		return in_array($vendorId, $this->_canSeePrice);
	}
	
	/**
	 *
	 * @return boolean
	 */
	public function isVendor()
	{
		return strtolower($this->getCustomerType()) === self::CUSTOMER_TYPE_VENDOR;
	}
	
	/**
	 *
	 * @return boolean
	 */
	public function isBuyer()
	{
		return strtolower($this->getCustomerType()) === self::CUSTOMER_TYPE_BUYER;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Customer_Model_Customer::sendNewAccountEmail()
	 */
	public function sendNewAccountEmail($type = 'registered', $backUrl = '', $storeId = '0')
	{
		$types = array();
		
		switch ($this->getCustomerType()) {
			case self::CUSTOMER_TYPE_VENDOR:
				$types = array(
						'registered'   => self::XML_PATH_VENDOR_REGISTER_EMAIL_TEMPLATE,
						'confirmed'    => self::XML_PATH_VENDOR_CONFIRMED_EMAIL_TEMPLATE,
						'confirmation' => self::XML_PATH_VENDOR_CONFIRM_EMAIL_TEMPLATE,
						'approved'     => self::XML_PATH_VENDOR_APPROVED_EMAIL_TEMPLATE,
				);
				break;
			default:
				$types = array(
						'registered'   => self::XML_PATH_REGISTER_EMAIL_TEMPLATE,  // welcome email, when confirmation is disabled
						'confirmed'    => self::XML_PATH_CONFIRMED_EMAIL_TEMPLATE, // welcome email, when confirmation is enabled
						'confirmation' => self::XML_PATH_CONFIRM_EMAIL_TEMPLATE,   // email with confirmation link
				);
		}
		
		if (!isset($types[$type])) {
			Mage::throwException(Mage::helper('customer')->__('Wrong transactional account email type'));
		}
	
		if (!$storeId) {
			$storeId = $this->_getWebsiteStoreId($this->getSendemailStoreId());
		}
	
		$this->_sendEmailTemplate($types[$type], self::XML_PATH_REGISTER_EMAIL_IDENTITY,
				array('customer' => $this, 'back_url' => $backUrl), $storeId);
	
		return $this;
	}

    public function validate()
    {
        $errors = array();
//        if (!Zend_Validate::is( trim($this->getFirstname()) , 'NotEmpty')) {
//            $errors[] = Mage::helper('customer')->__('The first name cannot be empty.');
//        }
//
//        if (!Zend_Validate::is( trim($this->getLastname()) , 'NotEmpty')) {
//            $errors[] = Mage::helper('customer')->__('The last name cannot be empty.');
//        }
        if (!Zend_Validate::is($this->getEmail(), 'EmailAddress')) {
            $errors[] = Mage::helper('customer')->__('Invalid email address "%s".', $this->getEmail());
        }

        $password = $this->getPassword();
        if (!$this->getId() && !Zend_Validate::is($password , 'NotEmpty')) {
            $errors[] = Mage::helper('customer')->__('The password cannot be empty.');
        }
        if (strlen($password) && !Zend_Validate::is($password, 'StringLength', array(6))) {
            $errors[] = Mage::helper('customer')->__('The minimum password length is %s', 6);
        }
        $confirmation = $this->getConfirmation();
        if ($password != $confirmation) {
            $errors[] = Mage::helper('customer')->__('Please make sure your passwords match.');
        }



        $entityType = Mage::getSingleton('eav/config')->getEntityType('customer');
        $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'dob');
        if ($attribute->getIsRequired() && '' == trim($this->getDob())) {
            $errors[] = Mage::helper('customer')->__('The Date of Birth is required.');
        }
        $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'taxvat');
        if ($attribute->getIsRequired() && '' == trim($this->getTaxvat())) {
            $errors[] = Mage::helper('customer')->__('The TAX/VAT number is required.');
        }
        $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'gender');
        if ($attribute->getIsRequired() && '' == trim($this->getGender())) {
            $errors[] = Mage::helper('customer')->__('Gender is required.');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }
}
