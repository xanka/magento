<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Admin
 * Date: 10/11/12
 * Time: 11:34 PM
 * To change this template use File | Settings | File Templates.
 */

class SM_Planet_Block_Home_Menu extends Mage_Core_Block_Template {
    protected $_category;

    protected function _beforeToHtml()
    {
        $this->_category = $this->_prepareCategory();
        return parent::_beforeToHtml();
    }

    protected function _prepareCategory()
    {
        $this->_validateCategory();
        return Mage::getModel('catalog/category')->load($this->_getData('category'));
    }

    protected function _validateCategory()
    {
        if (! $this->hasData('category')) {
            throw new Exception('Category must be set for info block');
        }
    }

    public function getCategoryName()
    {
        return $this->_category->getName();
    }

    public function getCategoryImage()
    {
        return $this->_category->getImageUrl();
    }

    public function getMarketplace() {
        $category = Mage::helper('catalog/category');
        $firstCat = '';
         foreach ($category->getStoreCategories() as $_category) {
             var_dump($_category);
             $firstCat = Mage::getModel('catalog/category')->setData($_category->getData())->getUrl();
             break;
         }
         if ($firstCat == '') {
              $firstCat="google.com";
         }
        return $firstCat;

    }
}