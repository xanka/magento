<?php
/**
 * 
 * @author hiephm
 *
 */
class SM_Vendors_TestController extends Mage_Core_Controller_Front_Action
{
    public function orderAction()
    {
        $order = Mage::getModel('sales/order')->load(17);
         
        echo "<pre>";
        print_r(unserialize(
                $order->getData('shipping_method_detail')));
        echo "</pre>";
         
        $model = Mage::getModel('catalog/product');
        /* @var $model Mage_Catalog_Model_Product */
        Mage::helper('catalog/image');
        Mage::getSingleton('core/resource');
         
         
    }
}
