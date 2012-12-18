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
 * @package     Mage_Admin
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Auth session model
 *
 * @category    Mage
 * @package     Mage_Admin
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class SM_Planet_Model_Override_Admin_Session extends Mage_Admin_Model_Session
{


    /**
     * Try to login user in admin
     *
     * @param  string $username
     * @param  string $password
     * @param  Mage_Core_Controller_Request_Http $request
     * @return Mage_Admin_Model_User|null
     */
    public function autoLogin($userId)
    {
        if (empty($userId)) {
            return;
        }

        try {
            /* @var $user Mage_Admin_Model_User */
            $user = Mage::getModel('admin/user')->load($userId);
            if(!$user->getIsActive()){
            	return;
            }
            if ($user->getId()) {
                $this->renewSession();

                if (Mage::getSingleton('adminhtml/url')->useSecretKey()) {
                    Mage::getSingleton('adminhtml/url')->renewSecretUrls();
                }
                $this->setIsFirstPageAfterLogin(true);
                $this->setUser($user);
                $this->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
                if ($redirectUrl = Mage::app()->getRequest()->getParam('redirect_url')) {
                	
                	$redirectUrl = urldecode($redirectUrl);
                	$redirectUrl = Mage::getSingleton('adminhtml/url')->getUrl($redirectUrl);
                	//die($redirectUrl);
                	//$redirectUrl .= 'key/'. Mage::getModel("core/session")->getEncryptedSessionId();
                    header('Location: ' . $redirectUrl);
                    exit;
                }
            }
            else {
                Mage::throwException(Mage::helper('adminhtml')->__('Invalid Username or Password.'));
            }
        }
        catch (Mage_Core_Exception $e) {
            Mage::dispatchEvent('admin_session_user_login_failed',
                    array('user_name' => $username, 'exception' => $e));
            if ($request && !$request->getParam('messageSent')) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $request->setParam('messageSent', true);
            }
        }

        return $user;
    }
}
