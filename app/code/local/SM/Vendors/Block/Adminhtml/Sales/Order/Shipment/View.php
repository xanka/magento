<?php
class SM_Vendors_Block_Adminhtml_Sales_Order_Shipment_View extends Mage_Adminhtml_Block_Sales_Order_Shipment_View
{

    public function __construct()
    {
        $this->_objectId    = 'shipment_id';
        $this->_controller  = 'sales_order_shipment';
        $this->_mode        = 'view';
        
        $grandParent = get_parent_class(get_parent_class($this));
        call_user_func(array($grandParent, '__construct'));

        $this->_removeButton('reset');
        $this->_removeButton('delete');
        if (Mage::getSingleton('admin/session')->isAllowed('smvendors/vendors_orders/actions/emails')) {
            $this->_updateButton('save', 'label', Mage::helper('sales')->__('Send Tracking Information'));
            $this->_updateButton('save',
                'onclick', "deleteConfirm('"
                . Mage::helper('sales')->__('Are you sure you want to send Shipment email to customer?')
                . "', '" . $this->getEmailUrl() . "')"
            );
        }

        if ($this->getShipment()->getId()) {
            $this->_addButton('print', array(
                'label'     => Mage::helper('sales')->__('Print'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.$this->getPrintUrl().'\')'
                )
            );
        }
    }

    public function getBackUrl()
    {
        return $this->getUrl(
            '*/vendors_order/view',
            array(
                'order_id'  => $this->getShipment()->getOrderId(),
                'active_tab'=> 'order_shipments'
            ));
    }

    public function getEmailUrl()
    {
        return $this->getUrl('*/vendors_order_shipment/email', array('shipment_id'  => $this->getShipment()->getId()));
    }

    public function updateBackButtonUrl($flag)
    {
        if ($flag) {
            if ($this->getShipment()->getBackUrl()) {
                return $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getShipment()->getBackUrl() . '\')');
            }
            return $this->_updateButton('back', 'onclick', 'setLocation(\'' . $this->getUrl('*/vendors_order_shipment/') . '\')');
        }
        return $this;
    }
}
