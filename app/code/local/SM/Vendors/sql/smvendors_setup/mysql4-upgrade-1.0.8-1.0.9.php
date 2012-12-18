<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$vendor_order_table = $installer->getTable('smvendors/order');

$installer->run("
        ALTER TABLE `{$installer->getTable('sales/order_status_history')}` ADD COLUMN `vendor_id` INT(10) NOT NULL DEFAULT 0;        
        ALTER TABLE `{$installer->getTable('sales/invoice')}` ADD COLUMN `vendor_id` INT(10) NOT NULL DEFAULT 0;
        ALTER TABLE `{$installer->getTable('sales/invoice_grid')}` ADD COLUMN `vendor_id` INT(10) NOT NULL DEFAULT 0;
        ALTER TABLE `{$installer->getTable('sales/shipment')}` ADD COLUMN `vendor_id` INT(10) NOT NULL DEFAULT 0;
        ALTER TABLE `{$installer->getTable('sales/shipment_grid')}` ADD COLUMN `vendor_id` INT(10) NOT NULL DEFAULT 0;
        ALTER TABLE `{$installer->getTable('sales/creditmemo')}` ADD COLUMN `vendor_id` INT(10) NOT NULL DEFAULT 0;
        ALTER TABLE `{$installer->getTable('sales/creditmemo_grid')}` ADD COLUMN `vendor_id` INT(10) NOT NULL DEFAULT 0;

        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `discount_canceled` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `discount_invoiced` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `discount_refunded` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `shipping_canceled` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `shipping_invoiced` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `shipping_refunded` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `shipping_tax_refunded` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `subtotal_canceled` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `subtotal_invoiced` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `subtotal_refunded` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `tax_canceled` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `tax_invoiced` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `tax_refunded` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_subtotal` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_discount_amount` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_tax_amount` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_shipping_amount` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_shipping_tax_amount` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_grand_total` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_discount_canceled` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_discount_invoiced` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_discount_refunded` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_shipping_canceled` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_shipping_invoiced` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_shipping_refunded` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_shipping_tax_refunded` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_subtotal_canceled` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_subtotal_invoiced` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_subtotal_refunded` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_tax_canceled` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_tax_invoiced` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_tax_refunded` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_total_canceled` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_total_invoiced` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_total_paid` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_total_refunded` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `total_canceled` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `total_invoiced` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `total_paid` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `total_refunded` DECIMAL(12,4) NOT NULL DEFAULT 0;
 
");

$installer->endSetup();