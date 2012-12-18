<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$vendor_order_table = $installer->getTable('smvendors/order');

$installer->run("
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `subtotal_incl_tax` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `shipping_incl_tax` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_subtotal_incl_tax` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_shipping_incl_tax` DECIMAL(12,4) NOT NULL DEFAULT 0;
");

$installer->endSetup();