<?php
/**
 * Date: 10/6/12
 */

class SM_Planet_Block_Home_Search extends Mage_Core_Block_Template {
    public function getCartLink() {
        $link = $this->getLayout()->createBlock('checkout/links');
        $count = $link->getSummaryQty() ? $link->getSummaryQty()
            : $this->helper('checkout/cart')->getSummaryCount();

        if ($count == 1) {
            $text = $this->__('My Basket (%s item)', $count);
        } elseif ($count > 0) {
            $text = $this->__('My Basket (%s items)', $count);
        } else {
            $text = $this->__('My Basket');
        }

        return $text;

    }
}