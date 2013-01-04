<?php
class SM_Vendors_Block_Adminhtml_Sales_Order_View_Tab_Invoices
    extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Invoices
{
    protected function _prepareCollection()
    {
        $vendorOrder = Mage::registry('vendor_order');
        if(!$vendorOrder) {
            return parent::_prepareCollection();
        } else {
            $grandParent = get_parent_class(get_parent_class($this));
            $collection = Mage::getResourceModel($this->_getCollectionClass())
                ->addFieldToSelect('entity_id')
                ->addFieldToSelect('created_at')
                ->addFieldToSelect('order_id')
                ->addFieldToSelect('increment_id')
                ->addFieldToSelect('state')
                ->addFieldToSelect('grand_total')
                ->addFieldToSelect('base_grand_total')
                ->addFieldToSelect('store_currency_code')
                ->addFieldToSelect('base_currency_code')
                ->addFieldToSelect('order_currency_code')
                ->addFieldToSelect('billing_name')
                ->setOrderFilter($this->getOrder())
                ->addFieldToFilter('vendor_id', $vendorOrder->getVendorId())
            ;
            $this->setCollection($collection);
            return call_user_func(array($grandParent, '_prepareCollection'));
        }
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/vendors_order_invoice/view',
            array(
                'invoice_id'=> $row->getId(),
                'order_id'  => $row->getOrderId()
            )
        );
    }
}
