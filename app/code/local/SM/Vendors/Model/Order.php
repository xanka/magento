<?php

class SM_Vendors_Model_Order extends Mage_Core_Model_Abstract {

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETE = 'complete';
    const STATUS_CANCELED = 'canceled';
    const STATUS_CLOSED = 'closed';

    protected $_eventPrefix = 'vendors_order';

    /**
     * @var Mage_Sales_Model_Resource_Order_Item_Collection
     */
    protected $_orderItems = null;

    /**
     * @var Mage_Sales_Model_Order
     */
    protected $_originOrder = null;

    /**
     * @var SM_Vendors_Model_Vendor
     */
    protected $_vendor = null;

    public function _construct() {
        parent::_construct();
        $this->_init('smvendors/order');
    }

    public function splitOrder($order) {
        $vendors = array();
        $shippingMethods = $order->getData('shipping_method_detail');

        $shippingMethods = ($shippingMethods ? unserialize($shippingMethods) : array());
        $entityType = Mage::getModel('eav/entity_type');
        $entityType->setEntityTypeCode('vendor_order');
        /* @var $order Mage_Sales_Model_Order */
        foreach ($order->getAllVisibleItems() as $item) {
            /* @var $item Mage_Sales_Model_Order_Item */
            $vendorId = $item->getVendorId();
            if (!isset($vendors[$vendorId])) {
                $vendor = Mage::getModel('smvendors/vendor')->load($vendorId);
                $vendors[$vendorId] = array(
                    'order_id' => $order->getId(),
                    'vendor_id' => $vendorId,
                    'commission' => $vendor->getVendorCommission(),
                    'status' => $order->getStatus(),
                    'subtotal' => $item->getRowTotal(),
                    'tax_amount' => $item->getTaxAmount(),
                    'discount_amount' => $item->getDiscountAmount(),
                    'discount_description' => '', // TODO Get description for each applied discount
                    'grand_total' => $item->getRowTotalInclTax()
                    - $item->getDiscountAmount(),
                );
                $entityType->setData('vendor_object', $vendor);
                $vendors[$vendorId]['increment_id'] = $entityType->fetchNewIncrementId();
                if (isset($shippingMethods[$vendorId])) {
                    $vendors[$vendorId]['shipping_amount'] = $shippingMethods[$vendorId]['method']['price'];
                    $vendors[$vendorId]['shipping_tax_amount'] = 0; // TODO Get tax for this shipping
                    $vendors[$vendorId]['shipping_method'] = $shippingMethods[$vendorId]['method']['method'];
                    $vendors[$vendorId]['shipping_method_title'] = $shippingMethods[$vendorId]['method']['method_title'];
                    $vendors[$vendorId]['shipping_carrier'] = $shippingMethods[$vendorId]['method']['carrier'];
                    $vendors[$vendorId]['shipping_carrier_title'] = $shippingMethods[$vendorId]['method']['carrier_title'];
                    $vendors[$vendorId]['grand_total'] += $shippingMethods[$vendorId]['method']['price'];
                }
            } else {
                $vendors[$vendorId]['subtotal'] += $item->getRowTotal();
                $vendors[$vendorId]['tax_amount'] += $item->getTaxAmount();
                $vendors[$vendorId]['discount_amount'] += $item->getDiscountAmount();
                $vendors[$vendorId]['grand_total'] += $item->getRowTotalInclTax()
                        - $item->getDiscountAmount();
            }
        }

        foreach ($vendors as $vendor) {
            $vendorOrder = Mage::getModel('smvendors/order')->setId(null);
            /* @var $vendorOrder SM_Vendors_Model_Order */
            $vendor['discount_amount'] *= -1;
            $vendorOrder->setData($vendor);
            // Calculate commision when invoicing.
            $vendorOrder->setData('commission_amount', $vendor['grand_total'] * $vendor['commission'] / 100);
            if (array_key_exists('shipping_amount', $vendor)) {
                $vendorOrder->setData('shipping_incl_tax', $vendor['shipping_amount'] + $vendor['shipping_tax_amount']);
                $vendorOrder->setData('base_shipping_incl_tax', $vendorOrder->getData('shipping_incl_tax'));
                $vendorOrder->setData('base_shipping_amount', $vendor['shipping_amount']);
                $vendorOrder->setData('base_shipping_tax_amount', $vendor['shipping_tax_amount']);
            }
            $vendorOrder->setData('base_subtotal', $vendor['subtotal']);
            $vendorOrder->setData('base_tax_amount', $vendor['tax_amount']);
            $vendorOrder->setData('base_discount_amount', $vendor['discount_amount']);
            $vendorOrder->setData('base_grand_total', $vendor['grand_total']);
            $vendorOrder->setData('subtotal_incl_tax', $vendor['subtotal'] + $vendor['tax_amount']);
            $vendorOrder->setData('base_subtotal_incl_tax', $vendorOrder->getData('subtotal_incl_tax'));
            $vendorOrder->setOriginalOrder($order);
            $vendorOrder->save();

            Mage::helper('smvendors/email')->sendNewOrderEmailToVendor($vendorOrder, $order);
//            if(Mage::getConfig()->getModuleConfig('SM_Reviews')->is('active', 'true')){
//                Mage::helper('smreviews')->sendEmailReviewVendor($order, $vendor['vendor_id']);
//            }
        }
    }

    public function getByOriginOrderId($orderId, $vendorId) {
        $vendorOrder = $this->getResource()->getByOriginOrderId($orderId, $vendorId);
        if (!empty($vendorOrder)) {
            $this->setData($vendorOrder);
        }
        return $this;
    }

    public function registerInvoice($invoice) {
        $this->setTotalInvoiced($this->getTotalInvoiced() + $invoice->getGrandTotal());
        $this->setBaseTotalInvoiced($this->getBaseTotalInvoiced() + $invoice->getBaseGrandTotal());

        $this->setSubtotalInvoiced($this->getSubtotalInvoiced() + $invoice->getSubtotal());
        $this->setBaseSubtotalInvoiced($this->getBaseSubtotalInvoiced() + $invoice->getBaseSubtotal());

        $this->setTaxInvoiced($this->getTaxInvoiced() + $invoice->getTaxAmount());
        $this->setBaseTaxInvoiced($this->getBaseTaxInvoiced() + $invoice->getBaseTaxAmount());

//         $this->setHiddenTaxInvoiced($this->getHiddenTaxInvoiced() + $invoice->getHiddenTaxAmount());
//         $this->setBaseHiddenTaxInvoiced($this->getBaseHiddenTaxInvoiced() + $invoice->getBaseHiddenTaxAmount());

        $this->setShippingTaxInvoiced($this->getShippingTaxInvoiced() + $invoice->getShippingTaxAmount());
        $this->setBaseShippingTaxInvoiced($this->getBaseShippingTaxInvoiced() + $invoice->getBaseShippingTaxAmount());


        $this->setShippingInvoiced($this->getShippingInvoiced() + $invoice->getShippingAmount());
        $this->setBaseShippingInvoiced($this->getBaseShippingInvoiced() + $invoice->getBaseShippingAmount());

        $this->setDiscountInvoiced($this->getDiscountInvoiced() + $invoice->getDiscountAmount());
        $this->setBaseDiscountInvoiced($this->getBaseDiscountInvoiced() + $invoice->getBaseDiscountAmount());
//         $this->setBaseTotalInvoicedCost($this->getBaseTotalInvoicedCost() + $invoice->getBaseCost());

        $this->setTotalPaid(
                $this->getTotalPaid() + $invoice->getGrandTotal()
        );
        $this->setBaseTotalPaid(
                $this->getBaseTotalPaid() + $invoice->getBaseGrandTotal()
        );

        $this->_registerCommission($invoice, 'commission_amount');

        $invoice->setVendorId($this->getVendorId());
    }

    public function registerShipment($shipment) {
        $shipment->setVendorId($this->getVendorId());
    }

    public function registerCreditmemo($creditmemo) {
        $orderRefund = Mage::app()->getStore()->roundPrice(
                $this->getTotalRefunded() + $creditmemo->getGrandTotal()
        );
        $baseOrderRefund = Mage::app()->getStore()->roundPrice(
                $this->getBaseTotalRefunded() + $creditmemo->getBaseGrandTotal()
        );

        $this->setBaseTotalRefunded($baseOrderRefund);
        $this->setTotalRefunded($orderRefund);

        $this->setBaseSubtotalRefunded($this->getBaseSubtotalRefunded() + $creditmemo->getBaseSubtotal());
        $this->setSubtotalRefunded($this->getSubtotalRefunded() + $creditmemo->getSubtotal());

        $this->setBaseTaxRefunded($this->getBaseTaxRefunded() + $creditmemo->getBaseTaxAmount());
        $this->setTaxRefunded($this->getTaxRefunded() + $creditmemo->getTaxAmount());
//         $this->setBaseHiddenTaxRefunded($this->getBaseHiddenTaxRefunded()+$creditmemo->getBaseHiddenTaxAmount());
//         $this->setHiddenTaxRefunded($this->getHiddenTaxRefunded()+$creditmemo->getHiddenTaxAmount());

        $this->setBaseShippingRefunded($this->getBaseShippingRefunded() + $creditmemo->getBaseShippingAmount());
        $this->setShippingRefunded($this->getShippingRefunded() + $creditmemo->getShippingAmount());

        $this->setBaseShippingTaxRefunded($this->getBaseShippingTaxRefunded() + $creditmemo->getBaseShippingTaxAmount());
        $this->setShippingTaxRefunded($this->getShippingTaxRefunded() + $creditmemo->getShippingTaxAmount());

        $this->setAdjustmentPositive($this->getAdjustmentPositive() + $creditmemo->getAdjustmentPositive());
        $this->setBaseAdjustmentPositive($this->getBaseAdjustmentPositive() + $creditmemo->getBaseAdjustmentPositive());

        $this->setAdjustmentNegative($this->getAdjustmentNegative() + $creditmemo->getAdjustmentNegative());
        $this->setBaseAdjustmentNegative($this->getBaseAdjustmentNegative() + $creditmemo->getBaseAdjustmentNegative());

        $this->setDiscountRefunded($this->getDiscountRefunded() + $creditmemo->getDiscountAmount());
        $this->setBaseDiscountRefunded($this->getBaseDiscountRefunded() + $creditmemo->getBaseDiscountAmount());

        if ($creditmemo->getDoTransaction()) {
            $this->setTotalOnlineRefunded(
                    $this->getTotalOnlineRefunded() + $creditmemo->getGrandTotal()
            );
            $this->setBaseTotalOnlineRefunded(
                    $this->getBaseTotalOnlineRefunded() + $creditmemo->getBaseGrandTotal()
            );
        } else {
            $this->setTotalOfflineRefunded(
                    $this->getTotalOfflineRefunded() + $creditmemo->getGrandTotal()
            );
            $this->setBaseTotalOfflineRefunded(
                    $this->getBaseTotalOfflineRefunded() + $creditmemo->getBaseGrandTotal()
            );
        }

        $this->_registerCommission($creditmemo, 'commission_amount_refunded');

        $creditmemo->setVendorId($this->getVendorId());
    }

    public function canInvoice() {
        if ($this->getStatus() === self::STATUS_CANCELED ||
                $this->getStatus() === self::STATUS_CLOSED ||
                $this->getStatus() === self::STATUS_COMPLETE) {
            return false;
        }

        foreach ($this->getAllVendorItems() as $item) {
            if ($item->getQtyToInvoice() > 0 && !$item->getLockedDoInvoice()) {
                return true;
            }
        }

        return false;
    }

    public function canCancel() {
        $allInvoiced = true;
        foreach ($this->getAllVendorItems() as $item) {
            if ($item->getQtyToInvoice()) {
                $allInvoiced = false;
                break;
            }
        }
        if ($allInvoiced) {
            return false;
        }

        if ($this->getStatus() === self::STATUS_CANCELED ||
                $this->getStatus() === self::STATUS_CLOSED ||
                $this->getStatus() === self::STATUS_COMPLETE) {
            return false;
        }

        return true;
    }

    public function isCanceled() {
        return ($this->getStatus() === self::STATUS_CANCELED);
    }

    public function canShip() {
        if ($this->getStatus() === self::STATUS_CANCELED ||
                $this->getStatus() === self::STATUS_CLOSED ||
                $this->getStatus() === self::STATUS_COMPLETE) {
            return false;
        }

        foreach ($this->getAllVendorItems() as $item) {
            if ($item->getQtyToShip() > 0 && !$item->getIsVirtual()
                    && !$item->getLockedDoShip()) {
                return true;
            }
        }

        return false;
    }

    public function canCreditmemo() {
        if ($this->getStatus() === self::STATUS_CANCELED || $this->getStatus() === self::STATUS_CLOSED) {
            return false;
        }

        if (abs(Mage::app()->getStore()->roundPrice($this->getTotalPaid()) - $this->getTotalRefunded()) < .0001) {
            return false;
        }

        return true;
    }

    public function canReorder() {
        $order = $this->getOriginalOrder();

        if (!$order->getId() || !$order->getCustomerId() || $order->isPaymentReview()) {
            return false;
        }

        $products = array();
        foreach ($this->getAllVendorItems() as $item) {
            $products[] = $item->getProductId();
        }

        if (!empty($products)) {
            foreach ($products as $productId) {
                $product = Mage::getModel('catalog/product')
                        ->setStoreId($order->getStoreId())
                        ->load($productId);
                if (!$product->getId() || !$product->isSalable()) {
                    return false;
                }
            }
        }

        return true;
    }

    public function getAllVendorItems() {
        if (!$this->getOrderId() || !$this->getVendorId()) {
            return array();
        }

        if ($this->_orderItems == null) {
            $this->_orderItems = Mage::getResourceModel('sales/order_item_collection')
                    ->setOrderFilter($this->getOrderId())
                    ->addFieldToFilter('vendor_id', $this->getVendorId())
            ;
        }

        return $this->_orderItems;
    }

    protected function _checkState() {
        if (!$this->getId()) {
            return $this;
        }

        if ($this->getStatus() !== self::STATUS_CANCELED
                && !$this->canInvoice()
                && !$this->canShip()) {
            if (0 == $this->getBaseGrandTotal() || $this->canCreditmemo()) {
                if ($this->getStatus() !== self::STATUS_COMPLETE) {
                    $this->_setStatusHistory(self::STATUS_COMPLETE);
                }
            }
            /**
             * Order can be closed just in case when we have refunded amount.
             * In case of "0" grand total order checking ForcedCanCreditmemo flag
             */ elseif (floatval($this->getTotalRefunded())) {
                if ($this->getStatus() !== self::STATUS_CLOSED) {
                    $this->_setStatusHistory(self::STATUS_CLOSED);
                }
            }
        }

        if ($this->getStatus() === self::STATUS_PENDING) {
            $this->setStatus(self::STATUS_PROCESSING);
        }
        return $this;
    }

    protected function _beforeSave() {
        $this->_checkState();
        return parent::_beforeSave();
    }

    public function registerCancel(Mage_Sales_Model_Order $order) {
        if ($this->canCancel()) {
            foreach ($this->getAllVendorItems() as $item) {
                $item->cancel();
            }

            $this->setSubtotalCanceled($this->getSubtotal() - $this->getSubtotalInvoiced());
            $this->setBaseSubtotalCanceled($this->getBaseSubtotal() - $this->getBaseSubtotalInvoiced());

            $this->setTaxCanceled($this->getTaxAmount() - $this->getTaxInvoiced());
            $this->setBaseTaxCanceled($this->getBaseTaxAmount() - $this->getBaseTaxInvoiced());

            $this->setShippingCanceled($this->getShippingAmount() - $this->getShippingInvoiced());
            $this->setBaseShippingCanceled($this->getBaseShippingAmount() - $this->getBaseShippingInvoiced());

            $this->setDiscountCanceled(abs($this->getDiscountAmount()) - $this->getDiscountInvoiced());
            $this->setBaseDiscountCanceled(abs($this->getBaseDiscountAmount()) - $this->getBaseDiscountInvoiced());

            $this->setTotalCanceled($this->getGrandTotal() - $this->getTotalPaid());
            $this->setBaseTotalCanceled($this->getBaseGrandTotal() - $this->getBaseTotalPaid());

            //Update original order:
            $order->setSubtotalCanceled($order->getSubtotalCanceled() + $this->getSubtotalCanceled());
            $order->setBaseSubtotalCanceled($order->getBaseSubtotalCanceled() + $this->getBaseSubtotalCanceled());

            $order->setTaxCanceled($order->getTaxCanceled() + $this->getTaxCanceled());
            $order->setBaseTaxCanceled($order->getBaseTaxCanceled() + $this->getBaseTaxCanceled());

            $order->setShippingCanceled($order->getShippingCanceled() + $this->getShippingCanceled());
            $order->setBaseShippingCanceled($order->getBaseShippingCanceled() + $this->getBaseShippingCanceled());

            $order->setDiscountCanceled($order->getDiscountCanceled() + $this->getDiscountCanceled());
            $order->setBaseDiscountCanceled($order->getBaseDiscountCanceled() + $this->getBaseDiscountCanceled());

            $order->setTotalCanceled($order->getTotalCanceled() + $this->getTotalCanceled());
            $order->setBaseTotalCanceled($order->getBaseTotalCanceled() + $this->getBaseTotalCanceled());

            $this->_setStatusHistory(self::STATUS_CANCELED);
        } else {
            Mage::throwException(Mage::helper('sales')->__('Order does not allow to be canceled.'));
        }
        return $this;
    }

    protected function _registerCommission($data, $field) {
        if ($field !== 'commission_amount' || $field !== 'commission_amount_refunded') {
            return $this;
        }

        $vendor = $this->getVendor();
        if ($vendor->getId()) {
            $commissionSubject = $data->getData('base_subtotal') - abs($data->getData('base_discount_amount'));
            $this->setData($field, $this->getData($field) + (($vendor->getCommission() * $commissionSubject) / 100));
        }
    }

    protected function _setStatusHistory($status, $comment = '') {
        $this->setStatus($status);
        $history = Mage::getModel('sales/order_status_history')
                ->setStatus($status)
                ->setComment($comment)
                ->setEntityName('order')
                ->setParentId($this->getOrderId())
                ->setVendorId($this->getVendorId())
                ->setIsCustomerNotified(0)
                ->save();
    }

    /**
     * @return SM_Vendors_Model_Vendor
     */
    public function getVendor() {
        if ($this->_vendor == null) {
            $this->_vendor = Mage::getModel('smvendors/vendor')->load($this->getVendorId());
        }

        return $this->_vendor;
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOriginalOrder() {
        if ($this->_originOrder == null) {
            $this->_originOrder = Mage::getModel('sales/order')->load($this->getOrderId());
        }

        return $this->_originOrder;
    }

    public function setOriginalOrder(Mage_Sales_Model_Order $originOrder) {
        $this->_originOrder = $originOrder;
    }

    public function getShippingDescription() {
        return $this->getShippingCarrierTitle() . ' - ' . $this->getShippingMethodTitle();
    }

    public function getStatusLabel() {
        return Mage::getSingleton('sales/order_config')->getStatusLabel($this->getStatus());
    }

}