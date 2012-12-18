<?php

class SM_Vendors_Block_Override_Adminhtml_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid {

    protected $_editUrl = '*/*/edit';

    protected function _construct() {
        parent::_construct();
        if (Mage::helper('core')->isModuleEnabled('SM_Easyproduct') &&
                Mage::helper('smvendors')->getVendorLogin()) {
            $this->_editUrl = 'easyproduct/adminhtml_product/edit';
        }
    }

    protected function _prepareCollection() {
        $store = $this->_getStore();
        $collection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('sku')
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('attribute_set_id')
                ->addAttributeToSelect('type_id');

        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $collection->joinField('qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left');
        }
        if ($store->getId()) {
            //$collection->setStoreId($store->getId());
            $adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
            $collection->addStoreFilter($store);
            $collection->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'inner', $adminStore);
            $collection->joinAttribute('custom_name', 'catalog_product/name', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $store->getId());
        } else {
            $collection->addAttributeToSelect('price');
            $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
            $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
        }
        // check user is vendor

        if ($vendor = Mage::helper('smvendors')->getVendorLogin()) {
            $collection->addAttributeToFilter('sm_product_vendor_id', $vendor->getId());
        } else {
            $collection->addAttributeToSelect('sm_product_vendor_id');
        }

        $this->setCollection($collection);
        $grandParent = $this->getGrandParent();
        call_user_func(array($grandParent, '_prepareCollection'));
        $this->getCollection()->addWebsiteNamesToResult();
        return $this;
    }

    protected function _prepareColumns() {
        $vendor = Mage::helper('smvendors')->getVendorLogin();
        $this->addColumn('entity_id', array(
            'header' => Mage::helper('catalog')->__('ID'),
            'width' => '50px',
            'type' => 'number',
            'index' => 'entity_id',
        ));
        $this->addColumn('name', array(
            'header' => Mage::helper('catalog')->__('Name'),
            'index' => 'name',
        ));

        $store = $this->_getStore();
        if ($store->getId()) {
            $this->addColumn('custom_name', array(
                'header' => Mage::helper('catalog')->__('Name in %s', $store->getName()),
                'index' => 'custom_name',
            ));
        }

        $this->addColumn('type', array(
            'header' => Mage::helper('catalog')->__('Type'),
            'width' => '60px',
            'index' => 'type_id',
            'type' => 'options',
            'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                ->load()
                ->toOptionHash();

        $this->addColumn('set_name', array(
            'header' => Mage::helper('catalog')->__('Attrib. Set Name'),
            'width' => '100px',
            'index' => 'attribute_set_id',
            'type' => 'options',
            'options' => $sets,
        ));

        $this->addColumn('sku', array(
            'header' => Mage::helper('catalog')->__('SKU'),
            'width' => '80px',
            'index' => 'sku',
        ));

        $store = $this->_getStore();
        $this->addColumn('price', array(
            'header' => Mage::helper('catalog')->__('Price'),
            'type' => 'price',
            'currency_code' => $store->getBaseCurrency()->getCode(),
            'index' => 'price',
        ));

        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $this->addColumn('qty', array(
                'header' => Mage::helper('catalog')->__('Qty'),
                'width' => '100px',
                'type' => 'number',
                'index' => 'qty',
            ));
        }

        // check user is vendor
        if (!$vendor) {

            $this->addColumn('sm_product_vendor_id', array(
                'header' => $this->__('Merchant Name'),
                'index' => 'sm_product_vendor_id',
                'type' => 'options',
                'options' => Mage::getResourceModel('smvendors/vendor_collection')->getOptionArray()
            ));
        }

        $this->addColumn('visibility', array(
            'header' => Mage::helper('catalog')->__('Visibility'),
            'width' => '70px',
            'index' => 'visibility',
            'type' => 'options',
            'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('catalog')->__('Status'),
            'width' => '70px',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('websites', array(
                'header' => Mage::helper('catalog')->__('Websites'),
                'width' => '100px',
                'sortable' => false,
                'index' => 'websites',
                'type' => 'options',
                'options' => Mage::getModel('core/website')->getCollection()->toOptionHash(),
            ));
        }

        $this->addColumn('action', array(
            'header' => Mage::helper('catalog')->__('Action'),
            'width' => '50px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => Mage::helper('catalog')->__('Edit'),
                    'url' => array(
                        'base' => $this->_editUrl,
                        'params' => array('store' => $this->getRequest()->getParam('store'))
                    ),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
        ));

        if (Mage::helper('catalog')->isModuleEnabled('Mage_Rss')) {
            $this->addRssList('rss/catalog/notifystock', Mage::helper('catalog')->__('Notify Low Stock RSS'));
        }
        $grandParent = $this->getGrandParent();
        return call_user_func(array($grandParent, '_prepareColumns'));
    }

    public function getGrandParent() {
        $grandParent = get_parent_class(get_parent_class($this));
        return $grandParent;
    }

    public function getRowUrl($row) {
        return $this->getUrl($this->_editUrl, array(
                    'store' => $this->getRequest()->getParam('store'),
                    'id' => $row->getId())
        );
    }

}
