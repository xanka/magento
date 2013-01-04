<?php
/**
 * Date: 11/9/12
 * Time: 2:02 AM
 */

class SM_Vendors_Block_Page_Category extends Mage_Core_Block_Template {
//    protected function _getCategory() {
//        $result = array();
//        $products = Mage::getModel('catalog/product')->getCollection();
//        foreach($products as $_product) {
//            $_product = Mage::getModel('catalog/product')->load($_product->getId());
//            $_category = $_product->getCategoryIds();
//            if (is_array($_category)) {
//                foreach($_category as $value) {
//                    if (!in_array($value,$result)) {
//                        $result[] = $value;
//                    }
//                }
//            }
//            if (!in_array($_product->getCategoryIds(),$result)) {
//                $result[] = $_product->getCategoryIds();
//            }
//        }
//        return $result;
//    }

//    public function getCategoryName() {
//
//        $categoryIds = $this->_getCategory();
//        var_dump($categoryIds);
//        foreach($categoryIds as $value) {
////            var_dump($value);
////            $category = Mage::getModel('catalog/catalog')->load($value);
////            echo $category->getName();
//        }
//
////        $category = Mage::getModel('catalog/catalog')->
//    }
}