<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Addon_Cs_Helper_System extends Fishpig_Wordpress_Helper_System
{
	public function getIntegrationTestResultsObserver(Varien_Event_Observer $observer)
	{
		$modules = (array)Mage::app()->getConfig()->getNode()->modules;
		
		if (isset($modules['Fishpig_WpCustomerSynch'])) {
			$title = 'Customer Synchronisation';
			$response = Mage::helper('wordpress')->__('You are using the new version of WordPress Customer Synchronisation but still have the old version installed. Please remove this by deleting the app/etc/modules/Fishpig_WpCustomerSynch.xml file and refreshing the cache.');
		
			$observer->getEvent()->getResults()->addItem($this->_createTestResultObject($title, $response, false));
		}
	}
}
