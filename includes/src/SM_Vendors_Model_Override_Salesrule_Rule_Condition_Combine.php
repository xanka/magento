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
 * @package     Mage_SalesRule
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class SM_Vendors_Model_Override_Salesrule_Rule_Condition_Combine extends Mage_SalesRule_Model_Rule_Condition_Combine
{
    protected $_allowConditions = array(
            '',
            'salesrule/rule_condition_product_found',
            'salesrule/rule_condition_product_subselect',
            'salesrule/rule_condition_combine',
            );
    
    public function getNewChildSelectOptions()
    {
        $conditions = parent::getNewChildSelectOptions();
        
        if(Mage::helper('smvendors')->getVendorLogin()) {
            foreach ($conditions as $key => $cond) {
                if(!in_array($cond['value'], $this->_allowConditions)) {
                    unset($conditions[$key]);
                }
            }
        }
        
        return $conditions;
    }
}
