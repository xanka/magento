<?php
$installer = $this;

$installer->startSetup();

$installer->run("
--
-- Table structure for table `sm_vendor`
--

DROP TABLE IF EXISTS {$this->getTable('smvendors/vendor')};
CREATE TABLE IF NOT EXISTS {$this->getTable('smvendors/vendor')} (
  `vendor_id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_name` varchar(1000) NOT NULL,
  `vendor_logo` varchar(1000) DEFAULT NULL,
  `vendor_commission` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `vendor_sale_postcodes` TEXT DEFAULT NULL,
  `vendor_status` smallint(4) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `vendor_shipping_methods` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`vendor_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Table structure for table `sm_vendor_page`
--

DROP TABLE IF EXISTS {$this->getTable('smvendors/page')};
CREATE TABLE IF NOT EXISTS {$this->getTable('smvendors/page')} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL,
  `title` text CHARACTER SET utf8,
  `content` text CHARACTER SET utf8,
  `meta_title` text CHARACTER SET utf8,
  `meta_desrciption` text CHARACTER SET utf8,
  `meta_keywords` text CHARACTER SET utf8,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `identifier` varchar(1000) CHARACTER SET utf8 NOT NULL,
  `root_template` varchar(1000) DEFAULT NULL,
  `creation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime DEFAULT NULL,
  `layout_update_xml` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

ALTER TABLE {$this->getTable('sales/quote_item')} ADD  `vendor_id` INT NULL;
ALTER TABLE {$this->getTable('sales/order_item')} ADD  `vendor_id` INT NULL;
");	

/* add later

ALTER TABLE `sales_flat_quote_shipping_rate` ADD `method_detail` TEXT NULL 
ALTER TABLE `sales_flat_quote_address` ADD `shipping_method_detail` TEXT NULL 
ALTER TABLE `sales_flat_order` ADD `shipping_method_detail` TEXT NULL 
ALTER TABLE  `sales_flat_quote_item` ADD  `vendor_id` INT NULL
ALTER TABLE  `sales_flat_order_item` ADD  `vendor_id` INT NULL
*/ 

/*
 * Add Customer Attribute 'customer_type'
 */
$installer->getConnection()->addColumn($this->getTable('customer/entity'), 'customer_type', "VARCHAR(255) DEFAULT NULL AFTER group_id");

/*
 * Add Customer Attribute 'customer_type'
 */
$attribute = Mage::getModel('eav/entity_attribute')
            	->loadByCode(Mage::getModel('eav/entity')->setType('customer')->getTypeId(), 'customer_type');

if (!$attribute->getId()) {
	$attribute = Mage::getModel('eav/entity_attribute');
}

$attribute
	->setEntityTypeId(Mage::getModel('eav/entity')->setType('customer')->getTypeId())
	->setAttributeCode('customer_type')
	->setBackendType(Mage::getModel('eav/entity_attribute')->TYPE_STATIC)
	->setFrontendInput('select')
	->setFrontendLabel('Customer type')
	->setSourceModel('smvendors/customer_attribute_source_type')
	->setIsGlobal(1)
	->setIsVisible(1)
	->setIsConfigureable(1)
	->setAttributeSetId(1)
	->setAttributeGroupId(1);

$attribute->save();

/*
 * Update Source Model as Magento might have put it to its own default
 */
if ($id = $attribute->getId()) {
	$installer->run("
		UPDATE {$this->getTable('eav_attribute')}
		SET source_model='smvendors/customer_attribute_source_type'
		WHERE attribute_id=".$id
	);
}

/*
 * Add attribute configuration for Magento 1.4+
 */
$version = explode('.', Mage::getVersion());
if (isset($version[0]) && isset($version[1]) && $version[0]==1 && $version[1]>=4) {
	$eavConfig = Mage::getSingleton('eav/config');
	if ($attribute = $eavConfig->getAttribute('customer', 'customer_type')) {
		$attribute->setData('used_in_forms', array('adminhtml_customer'));
		$attribute->setData('input_filter', '');
		$attribute->setData('multiline_count', 0);
		$attribute->setData('is_system', 1);
		$attribute->setData('sort_order', 101);
		$attribute->save();
	}
}

// add vendor attribute for product
$entityTypeId     = $installer->getEntityTypeId('catalog_product');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);

//$installer->addAttributeGroup($entityTypeId, $attributeSetId, 'Product Attachment', 6);

$installer->addAttribute('catalog_product', 'sm_product_vendor_id', array(
						'group'				=>'General',
                        'input'         => 'text',
						'label'         => 'Vendor',
						'required'      => 0,
						'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
						'visible'       => 1,
						'input_renderer'=> 'smvendors/adminhtml_catalog_product_render_vendor',
                    ));

$installer->endSetup();