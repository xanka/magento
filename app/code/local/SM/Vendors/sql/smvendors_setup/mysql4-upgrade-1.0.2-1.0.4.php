<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$installer->run("
	ALTER TABLE  {$installer->getTable('admin/user')} ADD  `vendor_id` INT NULL;
	ALTER TABLE  {$installer->getTable('smvendors/vendor_customer')} ADD  `order_id` INT NOT NULL
");
$installer->endSetup();