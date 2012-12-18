<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE  {$this->getTable('smvendors/vendor')} ADD  `vendor_prefix` VARCHAR( 10 ) NOT NULL;
ALTER TABLE  {$this->getTable('smvendors/vendor')} ADD  `vendor_total_orders` INT(10) NOT NULL DEFAULT 0;
");

$entityTypeId     = $installer->getEntityTypeId('catalog_product');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);

$installer->addAttributeGroup($entityTypeId, $attributeSetId, 'Permissions', 6);

$installer->endSetup();