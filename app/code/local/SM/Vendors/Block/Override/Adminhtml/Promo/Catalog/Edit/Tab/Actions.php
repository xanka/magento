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
 * description
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class SM_Vendors_Block_Override_Adminhtml_Promo_Catalog_Edit_Tab_Actions
    extends Mage_Adminhtml_Block_Promo_Catalog_Edit_Tab_Actions
{
    protected function _prepareForm()
    {
        
        parent::_prepareForm();
        
        $helper = Mage::helper('smvendors/form');
        /* @var $helper SM_Vendors_Helper_Form */
        $actionFieldset = $this->getForm()->getElement('action_fieldset');
        /* @var $actionFieldset Varien_Data_Form_Element_Fieldset */
        $vendorId = Mage::registry('current_promo_catalog_rule')->getData('vendor_id');
        
        if(($vendor = Mage::helper('smvendors')->getVendorLogin()) || $vendorId) {
            /* Do not allow stop other rules */
            $helper->turnSelectToHidden($actionFieldset, 'stop_rules_processing', 0);
            
            if(!$vendorId) {
                $vendorId = $vendor->getId();
            }
        }
        
        $helper->addHiddenField($actionFieldset, 'vendor_id', $vendorId ? $vendorId : 0);
        
        return $this;
    }
}
