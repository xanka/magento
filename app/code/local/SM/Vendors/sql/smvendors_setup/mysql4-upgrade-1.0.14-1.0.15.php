<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$vendor_order_table = $installer->getTable('smvendors/order');

$installer->run("
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `commission_amount_refunded` DECIMAL(12,4) NOT NULL DEFAULT 0;
");

$installer->endSetup();