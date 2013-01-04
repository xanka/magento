<?php
class SM_Vendors_Block_Adminhtml_Sales_Order_Creditmemo_View extends Mage_Adminhtml_Block_Sales_Order_Creditmemo_View
{
    /**
     * Retrieve back url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl(
            '*/vendors_order/view',
            array(
                'order_id'  => $this->getCreditmemo()->getOrderId(),
                'active_tab'=> 'order_creditmemos'
            ));
    }

    /**
     * Update 'back' button url
     *
     * @return Mage_Adminhtml_Block_Widget_Container | Mage_Adminhtml_Block_Sales_Order_Creditmemo_View
     */
    public function updateBackButtonUrl($flag)
    {
        if ($flag) {
            if ($this->getCreditmemo()->getBackUrl()) {
                return $this->_updateButton(
                    'back',
                    'onclick',
                    'setLocation(\'' . $this->getCreditmemo()->getBackUrl() . '\')'
                );
            }

            return $this->_updateButton(
                'back',
                'onclick',
                'setLocation(\'' . $this->getUrl('*/vendors_order_creditmemo/') . '\')'
            );
        }
        return $this;
    }

    /**
     * Check whether action is allowed
     *
     * @param string $action
     * @return bool
     */
    public function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('smvendors/vendors_orders/actions/' . $action);
    }
}
