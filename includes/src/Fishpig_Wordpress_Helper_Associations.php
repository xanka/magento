<?php
/**
 * @category		Fishpig
 * @package		Fishpig_Wordpress
 * @license		http://fishpig.co.uk/license.txt
 * @author		Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Helper_Associations extends Fishpig_Wordpress_Helper_Abstract
{
	/**
	 * Retrieve a collection of post's associated with the given product
	 *
	 * @param Mage_Catalog_Model_Product $product
	 * @return false|Fishpig_Wordpress_Model_Resource_Post_Collection_Abstract
	 */
	public function getAssociatedPostsByProduct(Mage_Catalog_Model_Product $product)
	{
		if (!($product instanceof Mage_Catalog_Model_Product)) {
			return false;
		}
		
		$associations = array_keys($this->getAssociations('product/post', $product->getId()));
		$categoryAssociations = array_keys($this->getAssociations('product/category', $product->getId()));
		$associations = array_merge($associations, $this->_convertWpCategoryIds($categoryAssociations));
		
		foreach($product->getCategoryIds() as $categoryId) {
			$associations = array_merge($associations, array_keys($this->getAssociations('category/post', $categoryId)));
			$categoryAssociations = array_keys($this->getAssociations('category/category', $categoryId));
			$associations = array_merge($associations, $this->_convertWpCategoryIds($categoryAssociations));
		}
		
		if (count($associations) > 0) {
			return Mage::getResourceModel('wordpress/post_collection')
				->addFieldToFilter('ID', array('IN' => $associations))
				->addIsPublishedFilter();
		}
		
		return false;
	}

	/**
	 * Retrieve a collection of post's associated with the given product
	 *
	 * @param Mage_Cms_Model_Page $page
	 * @return false|Fishpig_Wordpress_Model_Resource_Post_Collection_Abstract
	 */
	public function getAssociatedPostsByCmsPage(Mage_Cms_Model_Page $page)
	{
		if (!($page instanceof Mage_Cms_Model_Page)) {
			return false;
		}

		$associations = array_keys($this->getAssociations('cms_page/post', $page->getId(), 0));
		$categoryAssociations = array_keys($this->getAssociations('cms_page/category', $page->getId(), 0));
		$associations = array_merge($associations, $this->_convertWpCategoryIds($categoryAssociations));

		if (count($associations) > 0) {
			return Mage::getResourceModel('wordpress/post_collection')
				->addFieldToFilter('ID', array('IN' => $associations))
				->addIsPublishedFilter();
		}
		
		return false;
	}

	/** 
	 * Retrieve a collection of products assocaited with the post
	 *
	 * @param Fishpig_Wordpress_Model_Post $post
	 * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
	 */
	public function getAssociatedProductsByPost(Fishpig_Wordpress_Model_Post_Abstract $post)
	{
		if (!($post instanceof Fishpig_Wordpress_Model_Post)) {
			return false;
		}

		$associations = array_keys($this->getReverseAssociations('product/post', $post->getId()));

		foreach($post->getParentCategories() as $category) {
			$associations = array_merge($associations, array_keys($this->getReverseAssociations('product/category', $category->getId())));
		}

		$associations = array_unique($associations);

		if (count($associations) > 0) {
			$collection = Mage::getResourceModel('catalog/product_collection');
				
			Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

			$collection->addAttributeToFilter('status', 1);
			$collection->addAttributeToFilter('entity_id', array('in' => $associations));
		
			return $collection;
		}
		
		return false;
	}

	/**
	 * Retrieve an array of associated ID's and their position value
	 *
	 * @param string $type
	 * @param int $objectId
	 * @param int|null $storeId = null
	 * @return array|false
	 */
	public function getAssociations($type, $objectId, $storeId = null)
	{
		return $this->_getAssociations($type, $objectId, $storeId, 'object_id', 'wordpress_object_id');
	}
	
	/**
	 * Retrieve an array of associated ID's and their position value
	 * This receives a post ID and returns the associated Magento product ID's
	 *
	 * @param string $type
	 * @param int $objectId
	 * @param int|null $storeId = null
	 * @return array
	 */
	public function getReverseAssociations($type, $objectId, $storeId = null)
	{
		return $this->_getAssociations($type, $objectId, $storeId, 'wordpress_object_id', 'object_id');
	}
	
	/**
	 * Retrieve an array of associated ID's and their position value
	 *
	 * @param string $type
	 * @param int $objectId
	 * @param int|null $storeId = null
	 * @return array
	 */
	protected function _getAssociations($type, $objectId, $storeId = null, $filter, $return)
	{
		if (($typeId = $this->getTypeId($type)) !== false) {
			if (is_null($storeId)) {
				$storeId = Mage::app()->getStore()->getId();
			}

			$select = $this->_getReadAdapter()
				->select()
				->from($this->_getTable('wordpress/association'), array($return, 'position'))
				->where('type_id=?', $typeId)
				->where($filter . '=?', $objectId)
				->where('store_id=?', $storeId)
				->order('position ASC');
			
			try {
				if (($results = $this->_getReadAdapter()->fetchAll($select)) !== false) {
					$associations = array();
					
					foreach($results as $result) {
						$associations[$result[$return]] = $result['position'];
					}
	
					return $associations;
				}
			}
			catch (Exception $e) {
				$this->log($e);
			}
		}
		
		return array();
	}

	/**
	 * Delete all associations for a type/object_id/store combination
	 *
	 * @param string $type
	 * @param int $objectId
	 * @param int|null $storeId = null
	 * @return $this
	 */
	public function deleteAssociations($type, $objectId, $storeId = null)
	{	
		if (($typeId = $this->getTypeId($type)) !== false) {
			if (is_null($storeId)) {
				$storeId = Mage::app()->getStore()->getId();
			}
			
			$write = $this->_getWriteAdapter();
			$table = $this->_getTable('wordpress/association');
			
			$cond = implode(' AND ', array(
				$write->quoteInto('object_id=?', $objectId),
				$write->quoteInto('type_id=?', $typeId),
				$write->quoteInto('store_id=?', $storeId),
			));

			$write->delete($table, $cond, $storeId);
			$write->commit();
		}
		
		return $this;
	}
	
	/**
	 * Create associations
	 *
	 * @param string $type
	 * @param int $objectId
	 * @param array $associations
	 * @param int|null $storeId = null
	 * @return $this
	 */
	public function createAssociations($type, $objectId, array $associations, $storeId = null)
	{
		if (count($associations) === 0) {
			return $this;
		}
		
		if (($typeId = $this->getTypeId($type)) !== false) {
			if (is_null($storeId)) {
				$storeId = Mage::app()->getStore()->getId();
			}
			
			$write = $this->_getWriteAdapter();
			$table = $this->_getTable('wordpress/association');

			foreach($associations as $wpObjectId => $data) {
				if (is_array($data)) {
					$position = array_shift($data);
				}
				else {
					$position = 0;
					$wpObjectId = $data;
				}
				
				$write->insert($table, array(
					'type_id' => $typeId,
					'object_id' => $objectId,
					'wordpress_object_id' => $wpObjectId,
					'position' => $position,
					'store_id' => $storeId,
				));

				$write->commit();
			}
		}
		
		return $this;
	}

	/**
	 * Process an observer triggered to save associations
	 * This only works for certain models
	 *
	 * @param Varien_Event_Observer $observer
	 * @return bool
	 */
	public function processObserver(Varien_Event_Observer $observer)
	{
		$storeIds = $this->getAssociationStoreIds();
		
		if (($product = $observer->getEvent()->getProduct()) !== null) {
			$objectId = $product->getId();
		}
		else if (($category = $observer->getEvent()->getCategory()) !== null) {
			$objectId = $category->getId();
		}
		else if ($observer->getEvent()->getObject() instanceof Mage_Cms_Model_Page) {
			$objectId = $observer->getEvent()->getObject()->getId();
			$storeIds = array(0);
		}
		else {
			return false;
		}

		$post = Mage::app()->getRequest()->getPost('wp');
		
		if (!isset($post['assoc'])) {
			return false;
		}
		
		$assocData = $post['assoc'];

		foreach($assocData as $object => $data) {
			foreach($data as $wpObject => $associations) {
				$associations = Mage::helper('adminhtml/js')->decodeGridSerializedInput($associations);
				$type = $object . '/' . $wpObject;
		
				foreach($storeIds as $storeId) {
					$this->deleteAssociations($type, $objectId, $storeId)
						->createAssociations($type, $objectId, $associations, $storeId);
				}
			}
		}
	}
	
	/**
	 * Retrieve a type_id associated with the given type
	 *
	 * @param string $type
	 * @return int|false
	 */
	public function getTypeId($type)
	{
		if (strpos($type, '/') !== false) {
			$types = explode('/', $type);
			
			$select = $this->_getReadAdapter()
				->select()
				->from($this->_getTable('wordpress/association_type'), 'type_id')
				->where('object=?', $types[0])
				->where('wordpress_object=?', $types[1])
				->limit(1);
				
			return $this->_getReadAdapter()->fetchOne($select);
		}
		
		return false;
	}

	/**
	 * Add the position value for the association between each item and the $type and $objectId
	 * combination
	 *
	 * @param $collection
	 * @param string $type
	 * @param int $objectId
	 * @return $this
	 */	
	public function addRelatedPositionToSelect($collection, $type, $objectId, $storeId = null)
	{
		if (($typeId = Mage::helper('wordpress/associations')->getTypeId($type)) !== false) {
			if (is_null($storeId)) {
				$storeId = array((int)Mage::app()->getStore()->getId(), 0);
			}
			else if (!is_array($storeId)) {
				$storeId = array($storeId, 0);
			}
			
			$field = strpos($type, '/category') !== false ? 'term_id' : 'ID';

			$cond = implode(' AND ', array(
				'`assoc`.`wordpress_object_id` = `main_table`.`' . $field . '`',
				'`assoc`.`type_id` = ' . (int)$typeId,
				$collection->getConnection()->quoteInto('`assoc`.`store_id` IN (?)', $storeId),
			));
			
			$dbname = $collection->getTable('wordpress/association');
			
			if (!Mage::helper('wordpress/database')->isSameDatabase()) {
				$dbname = (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/dbname') . '.' . $dbname;
			}
			
			$collection->getSelect()
				->distinct()
				->joinLeft(
					array('assoc' => $dbname),
					$cond, 
					array('associated_position' => 'IF(ISNULL(position), 4444, position)', 'is_associated' => 'IF(ISNULL(assoc.position), 0, 1)')
				);
				
			$collection->getSelect()->order('assoc.store_id DESC');
//				->order('assoc.position ASC');
		}
		
		return $this;
	}

	/**
	 * Retrieve the current store ID
	 * If no store ID is set or site is multistore, return default store ID
	 *
	 * @return int
	 */
	public function getAssociationStoreIds()
	{
		$singleStore = Mage::app()->isSingleStoreMode() && Mage::helper('wordpress')->forceSingleStore();
			
		if (!$singleStore && ($storeId = (int)Mage::app()->getRequest()->getParam('store'))) {
			return array($storeId);
		}

		$select = $this->_getReadAdapter()
			->select()
			->from($this->_getTable('core/store'), 'store_id')
			->where('store_id>?', 0);
					
		return $this->_getReadAdapter()->fetchCol($select);
	}

	/**
	 * Retrieve a single store ID
	 *
	 * @return int
	 */
	public function getSingleStoreId()
	{
		$storeIds = $this->getAssociationStoreIds();
		
		if (is_array($storeIds)) {
			return (int)array_shift($storeIds);
		}
		
		return (int)$storeIds;
	}

	/**
	 * Convert an array of WordPress category ID's to
	 * an array of post ID's
	 *
	 * @param array $categoryIds
	 * @return array
	 */
	protected function _convertWpCategoryIds(array $categoryIds)
	{
		if (count($categoryIds) === 0) {
			return array();
		}

		$select = $this->_getReadAdapter()
			->select()
			->from(array('term' => $this->_getTable('wordpress/term')), '')
			->where('term.term_id IN (?)', $categoryIds);
			
		$select->join(
			array('tax' => $this->_getTable('wordpress/term_taxonomy')),
			"`tax`.`term_id` = `term`.`term_id` AND `tax`.`taxonomy`= 'category'",
			''
		);
		
		$select->join(
			array('rel' => $this->_getTable('wordpress/term_relationship')),
			"`rel`.`term_taxonomy_id` = `tax`.`term_taxonomy_id`",
			'object_id'
		);

		return $this->_getReadAdapter()->fetchCol($select);
	}

	/**
	 * Retrieve the read DB adapter
	 *
	 * @return
	 */	
	protected function _getReadAdapter()
	{
		return Mage::getSingleton('core/resource')->getConnection('core_read');
	}
	
	/**
	 * Retrieve the write DB adapter
	 *
	 * @return
	 */
	protected function _getWriteAdapter()
	{
		return Mage::getSingleton('core/resource')->getConnection('core_write');
	}
	
	/**
	 * Retrieve a table name by entity
	 *
	 * @param string $entity
	 * @return string
	 */	
	protected function _getTable($entity)
	{
		return Mage::getSingleton('core/resource')->getTableName($entity);	
	}
}
