<?php
/**
 * @category    SM
 * @package     SM_Sales
 * @copyright   Copyright (c) 2012 SmartOSC (www.smartosc.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
require_once 'Mage/Sales/controllers/OrderController.php';

/**
 * Sales orders controller
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class SM_Sales_OrderController extends Mage_Sales_OrderController
{

    public function historyAction()
    {
        parent::historyAction();
    }

    /**
     * Customer order history
     */
    public function invoicesAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__('My Invoices'));

        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->renderLayout();
    }

    /**
     * 
     */
    public function viewInvoiceAction()
    {
        $invoiceId = (int) $this->getRequest()->getParam('invoice_id');

        if (!$invoiceId) {
            return parent::invoiceAction();
        }

        $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
        $order = $invoice->getOrder();

        if ($this->_canViewOrder($order)) {
            Mage::register('current_order', $order);
            if (isset($invoice)) {
                Mage::register('current_invoice', $invoice);
            }

            $this->loadLayout();
            $this->_initLayoutMessages('catalog/session');

            $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
            if ($navigationBlock) {
                $navigationBlock->setActive('sales/order/invoices');
            }

            $this->renderLayout();
        } else {
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->_redirect('*/*/history');
            } else {
                $this->_redirect('sales/guest/form');
            }
        }
    }

    /**
     * Print Invoice Action
     */
    public function printInvoiceAction()
    {
        $invoiceId = (int) $this->getRequest()->getParam('invoice_id');

        if ($invoiceId) {
            $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
            $order = $invoice->getOrder();
        } else {
            $orderId = (int) $this->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($orderId);
        }

        if ($this->_canViewOrder($order)) {
            $invoices = array();
            if (isset($invoice)) {
                $invoices[$invoice->getId()] = $invoice;
            } else {
                $orderInvoices = $order->getInvoiceCollection();
                $invoices = array();

                foreach ($orderInvoices as $invoice) {
                    $invoices[$invoice->getId()] = $invoice;
                }
            }

            $order->setInvoices($invoices);
            $orders[$order->getId()] = $order;

            Mage::register('current_orders', $orders);

            $this->loadLayout('print');
            $this->renderLayout();
        } else {
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->_redirect('*/*/history');
            } else {
                $this->_redirect('sales/guest/form');
            }
        }
    }

    /**
     * Print invoices
     */
    public function printInvoicesAction()
    {
        $invoiceIds = $this->getRequest()->getParam('invoice_ids');

        $invoices = Mage::getResourceModel('sales/order_invoice_collection')
            ->addFieldToFilter('entity_id', $invoiceIds);

        $orders = array();
        $orderInvoices = array();

        foreach ($invoices as $id => $invoice) {
            $order = $invoice->getOrder();

            if ($this->_canViewOrder($order)) {
                $orderInvoices[$order->getId()][$invoice->getId()] = $invoice;

                if (!isset($orders[$order->getId()])) {
                    $orders[$order->getId()] = $order;
                }
            }
        }

        foreach ($orders as $orderId => $order) {
            if (isset($orderInvoices[$orderId])) {
                $order->setInvoices($orderInvoices[$orderId]);
            }
        }

        if ($invoices->count()) {
            Mage::register('current_orders', $orders);

            $this->loadLayout('print');
            $this->renderLayout();
        } else {
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->_redirect('*/*/history');
            } else {
                $this->_redirect('sales/guest/form');
            }
        }
    }

    public function printOrdersAction()
    {
        $orderIds = $this->getRequest()->getParam('order_ids');
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', $orderIds);

        if ($orders->count()) {
            Mage::register('current_orders', $orders);

            $this->loadLayout('print');
            $this->renderLayout();
        } else {
            $this->_redirect('*/*/history');
        }
    }
    
    /**
     * Try to load valid order by order_id and register it
     *
     * @param int $orderId
     * @return bool
     */
    protected function _loadValidOrder($orderId = null)
    {
    	if (null === $orderId) {
    		$orderId = (int) $this->getRequest()->getParam('order_id');
    	}
    	if (!$orderId) {
    		$this->_forward('noRoute');
    		return false;
    	}
    
    	
    	$vendorOrder = Mage::getModel('smvendors/order')->load($orderId);
//    	$order = Mage::getModel('sales/order')->load($vendorOrder->getOrderId());

        $order = Mage::getModel('sales/order')->load($orderId);
    	if ($this->_canViewOrder($order)) {
    		Mage::register('current_order', $order);
    		Mage::register('current_vendor_order', $vendorOrder);
    		return true;
    	} else {
    		$this->_redirect('*/*/history');
    	}
    	return false;
    }
}
