<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
		ALTER TABLE  {$installer->getTable('smvendors/vendor_customer')} DROP COLUMN `order_id`;
		ALTER IGNORE TABLE  {$installer->getTable('smvendors/vendor_customer')} ADD UNIQUE KEY `UNQ_VENDOR_CUSTOMER` (`vendor_id`, `customer_id`);
		ALTER TABLE  {$installer->getTable('smvendors/vendor_customer')} ADD COLUMN `contacted_for_price` TINYINT(2) DEFAULT 0;
");

$installer->endSetup();