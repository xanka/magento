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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales order details block
 *
 * @category    SM
 * @package     SM_Sales
 * @author      CED Team <core@magentocommerce.com>
 */

class SM_Sales_Block_Order_Print_Orders extends Mage_Sales_Block_Items_Abstract
{
    protected function _prepareLayout()
    {
    }

    public function getPaymentInfoHtml($order)
    {
        return $this->helper('payment')->getInfoBlock($order->getPayment());
    }

    public function getOrders()
    {
        if (!($orders = Mage::registry('current_orders'))) {
            $orders = array(Mage::registry('current_order'));
        }

        return $orders;
    }

    protected function _prepareItem(Mage_Core_Block_Abstract $renderer)
    {
        $renderer->setPrintStatus(true);
        return parent::_prepareItem($renderer);
    }

    public function getOrderTotalsHtml($order)
    {
        $html = '';
        $totals = $this->getChild('order_totals');

        if ($totals) {
            $totals->setOrder($order);

            $html = $totals->toHtml();
        }

        return $html;
    }
}

