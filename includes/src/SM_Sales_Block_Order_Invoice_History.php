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
 * Sales order history block
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class SM_Sales_Block_Order_Invoice_History extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sales/order/invoice/history.phtml');
        
        Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('sales')->__('My Invoices'));

        $invoices = Mage::getResourceModel('sales/order_invoice_collection');

        $invoices->addFieldToSelect('*');
        $invoices->getSelect()->joinInner(array('sales_order' => $invoices->getTable('sales/order')), 
                                            'sales_order.entity_id = main_table.order_id', 
                                            array('sales_order.total_item_count', 'sales_order.increment_id AS order_increment_id'));
        $invoices
            ->addFieldToFilter('sales_order.customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
            ->addFieldToFilter('sales_order.state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()));

        $this->setInvoices($invoices);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'sales.order.invoice.history.pager')
            ->setCollection($this->getInvoices());
        $this->setChild('pager', $pager);

        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getOrderViewUrl($invoice)
    {
        return $this->getUrl('*/*/invoice', array('order_id' => $invoice->getOrderId()));
    }

    public function getViewUrl($invoice)
    {
        return $this->getUrl('*/*/invoice', array('order_id' => $invoice->getOrderId(), 'invoice_id' => $invoice->getId()));
    }

    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    public function getPrintUrl()
    {
        return $this->getUrl('*/*/printInvoices');
    }
}
