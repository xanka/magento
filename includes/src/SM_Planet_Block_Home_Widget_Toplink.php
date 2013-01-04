<?php
/**
 * Date: 10/16/12
 * Time: 10:45 PM
 */

class SM_Planet_Block_Home_Widget_Toplink extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface {
    public function getMarketplaceurl() {
        $category = Mage::helper('catalog/category');
        $firstCat = '';
        foreach ($category->getStoreCategories() as $_category) {
            if ($_category->getName() != 'marketplace') continue;
            $firstCat = Mage::getModel('catalog/category')->setData($_category->getData())->getUrl();
            break;
        }
        if ($firstCat == '') {
            $firstCat="google.com";
        }
        return $firstCat;
    }
}