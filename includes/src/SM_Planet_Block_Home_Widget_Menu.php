<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Admin
 * Date: 10/11/12
 * Time: 11:33 PM
 * To change this template use File | Settings | File Templates.
 */

class SM_Planet_Block_Home_Widget_Menu extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface {
//    protected function _toHtml() {
//        return "this is my first widget";
//    }

    public function getMarketplaceurl() {
        $category = Mage::helper('catalog/category');
        $firstCat = '';
        foreach ($category->getStoreCategories() as $_category) {
            if ($_category->getName() != 'Marketplace') continue;
            $firstCat = Mage::getModel('catalog/category')->setData($_category->getData())->getUrl();
            break;
        }
        if ($firstCat == '') {
            $firstCat="google.com";
        }
        return $firstCat;
    }
}