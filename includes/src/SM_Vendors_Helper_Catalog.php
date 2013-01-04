<?php

class SM_Vendors_Helper_Catalog extends Mage_Core_Helper_Abstract
{
	/**
	 * Return current category path or get it from current category
	 * and creating array of categories|product paths for breadcrumbs
	 *
	 * @return string
	 */
	public function getBreadcrumbPath($vendor)
	{
		if (!$this->_categoryPath) {
			$path = array();
				
			if ($category = $this->getCategory()) {
				$pathInStore = $category->getPathInStore();
				$pathIds = array_reverse(explode(',', $pathInStore));

				$categories = $category->getParentCategories();

				// add category path breadcrumb
				foreach ($pathIds as $categoryId) {
					if (isset($categories[$categoryId]) && $categories[$categoryId]->getName()) {
						$url = '';
						if ($this->_isCategoryLink($categoryId)) {
							$url = $this->_getUrl('*/*/*',
									array(
											'id' => $vendor->getId(), 
											'_query' => array('cat' => $categories[$categoryId]->getId())
									));
						}

						$path['category'.$categoryId] = array(
								'label' => $categories[$categoryId]->getName(),
								'link' => $url,
						);
					}
				}
			}

			if ($this->getProduct()) {
				$path['product'] = array('label'=>$this->getProduct()->getName());
			}

			$this->_categoryPath = $path;
		}
		return $this->_categoryPath;
	}

	/**
	 * Return current category object
	 *
	 * @return Mage_Catalog_Model_Category|null
	 */
	public function getCategory()
	{
		return Mage::registry('current_category');
	}

	/**
	 * Retrieve current Product object
	 *
	 * @return Mage_Catalog_Model_Product|null
	 */
	public function getProduct()
	{
		return Mage::registry('current_product');
	}

	/**
	 * Check is category link
	 *
	 * @param int $categoryId
	 * @return bool
	 */
	protected function _isCategoryLink($categoryId)
	{
		if ($this->getProduct()) {
			return true;
		}
		if ($categoryId != $this->getCategory()->getId()) {
			return true;
		}
		return false;
	}
}