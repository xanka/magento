<?php
class SM_Vendors_Block_Adminhtml_Sales_Order_Invoice_View extends Mage_Adminhtml_Block_Sales_Order_Invoice_View
{
    public function getBackUrl()
    {
        return $this->getUrl(
            '*/vendors_order/view',
            array(
                'order_id'  => $this->getInvoice()->getOrderId(),
                'active_tab'=> 'order_invoices'
            ));
    }

    public function getCreditMemoUrl()
    {
        return $this->getUrl('*/vendors_order_creditmemo/start', array(
            'order_id'  => $this->getInvoice()->getOrder()->getId(),
            'invoice_id'=> $this->getInvoice()->getId(),
        ));
    }

    public function updateBackButtonUrl($flag)
    {
        if ($flag) {
            if ($this->getInvoice()->getBackUrl()) {
                return $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getInvoice()->getBackUrl() . '\')');
            }
            return $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/vendors_order_invoice/') . '\')');
        }
        return $this;
    }

    /**
     * Check whether is allowed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        return $this->_session->isAllowed('smvendors/vendors_orders/actions/' . $action);
    }
}
