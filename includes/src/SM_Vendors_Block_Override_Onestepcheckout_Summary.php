<?php

class SM_Vendors_Block_Override_Onestepcheckout_Summary extends Mage_Checkout_Block_Onepage_Abstract {

    protected $_rates;

    public function __construct() {
        $this->getQuote()->collectTotals()->save();
    }

    public function getItems() {
        return $this->getQuote()->getAllVisibleItems();
    }

    public function getTotals() {
        return $this->getQuote()->getTotals();
    }

    /**
     * 
     * @var Array
     */
    protected $_vendorItems;
    protected $_vendorTotals = array();

    /**
     * 
     * @return Array
     */
    public function getVendorItems() {
        $items = $this->getItems();

        if (empty($this->_vendorItems)) {
            $this->_vendorItems = array();
            foreach ($items as $item) {
                if (empty($this->_vendorItems[$item->getVendorId()])) {
                    $this->_vendorItems[$item->getVendorId()] = array();
                }

                $this->_vendorItems[$item->getVendorId()][] = $item;
            }
        }

        return $this->_vendorItems;
    }

    public function getVendorTotals($vendorId) {
        if (empty($this->_vendorTotals[$vendorId])) {
            $vendorItems = $this->getVendorItems();
            if (!empty($vendorItems[$vendorId])) {
                $vendorItems = $vendorItems[$vendorId];

                if (empty($this->_vendorTotals[$vendorId])) {
                    $this->_vendorTotals[$vendorId] = array(
                        'qty' => 0,
                        'subtotal' => 0,
                        'discount' => 0
                    );
                }
                foreach ($vendorItems as $item) {
                    $this->_vendorTotals[$vendorId]['subtotal'] += $item->getRowTotal();
                    $this->_vendorTotals[$vendorId]['tax'] += $item->getTaxAmount();
                }

                $this->_vendorTotals[$vendorId]['shipping_price'] = 0;
                $this->_vendorTotals[$vendorId]['subtotal'] += $this->_vendorTotals[$vendorId]['shipping_price'];
                $this->_vendorTotals[$vendorId]['subtotal'] += $this->_vendorTotals[$vendorId]['tax'];
            }
        }
        return $this->_vendorTotals[$vendorId];
    }

    /**
     * 
     * @param int $vendorId
     * @return SM_Vendors_Model_Vendor
     */
    public function getVendor($vendorId) {
        $vendor = Mage::getModel('smvendors/vendor')->load($vendorId);
        return $vendor;
    }

}
