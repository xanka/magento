<?php
/**
 * Author : Nguyen Trung Hieu
 * Email : hieunt@smartosc.com
 * Date: 8/29/12
 * Time: 1:18 PM
 */

$installer = $this;

$installer->startSetup();
$installer->run("
	--
	-- ADD column `vendor_id`,`vendor` for  ` sales_refunded_aggregated_order` and change constraint of this table
	--

    ALTER TABLE {$installer->getTable('sales/refunded_aggregated_order')}
    ADD vendor_id int;

    ALTER TABLE {$installer->getTable('sales/refunded_aggregated_order')}
    ADD vendor varchar(30);

    ALTER TABLE  {$installer->getTable('sales/refunded_aggregated_order')} DROP INDEX `UNQ_SALES_REFUNDED_AGGREGATED_ORDER_PERIOD_STORE_ID_ORDER_STATUS` ,
    ADD UNIQUE  `UNQ_SALES_REFUNDED_AGGREGATED_ORDER_PERIOD_STORE_ID_ORDER_STATUS` (  `period` ,  `store_id` ,  `order_status` ,  `vendor_id` );

    --
	-- ADD column `vendor_id`,`vendor` for  ` sales_refunded_aggregated` and  change constraint of this table
	--

	ALTER TABLE {$installer->getTable('sales/refunded_aggregated')}
    ADD vendor_id int;

    ALTER TABLE {$installer->getTable('sales/refunded_aggregated')}
    ADD vendor varchar(30);

    ALTER TABLE  {$installer->getTable('sales/refunded_aggregated')} DROP INDEX  `UNQ_SALES_REFUNDED_AGGREGATED_PERIOD_STORE_ID_ORDER_STATUS` ,
    ADD UNIQUE  `UNQ_SALES_REFUNDED_AGGREGATED_PERIOD_STORE_ID_ORDER_STATUS` (  `period` ,  `store_id` ,  `order_status` ,  `vendor_id` );

    --
	-- ADD column `vendor_id`,`vendor` for  ` sales_invoiced_aggregated` and change constraint of this table
	--
	ALTER TABLE {$installer->getTable('sales/invoiced_aggregated')}
    ADD vendor_id int;

    ALTER TABLE {$installer->getTable('sales/invoiced_aggregated')}
    ADD vendor varchar(30);

    ALTER TABLE {$installer->getTable('sales/invoiced_aggregated')} DROP INDEX `UNQ_SALES_INVOICED_AGGREGATED_PERIOD_STORE_ID_ORDER_STATUS` ,
    ADD UNIQUE  `UNQ_SALES_INVOICED_AGGREGATED_PERIOD_STORE_ID_ORDER_STATUS` (  `period` ,  `store_id` ,  `order_status`, `vendor_id`  );

    --
	-- ADD column `vendor_id`,`vendor` for  ` sales_invoiced_aggregated_order`   and change constraint of this table
	--

	ALTER TABLE {$installer->getTable('sales/invoiced_aggregated_order')}
    ADD vendor_id int;

    ALTER TABLE {$installer->getTable('sales/invoiced_aggregated_order')}
    ADD vendor varchar(30);

    ALTER TABLE {$installer->getTable('sales/invoiced_aggregated_order')} DROP INDEX `UNQ_SALES_INVOICED_AGGREGATED_ORDER_PERIOD_STORE_ID_ORDER_STATUS` ,
    ADD UNIQUE  `UNQ_SALES_INVOICED_AGGREGATED_ORDER_PERIOD_STORE_ID_ORDER_STATUS` (  `period` ,  `store_id` ,  `order_status` ,`vendor_id` );

    --
	-- ADD column `vendor_id`,`vendor` for  ` tax_order_aggregated_created`   and change constraint of this table
	--

	ALTER TABLE {$installer->getTable('tax/tax_order_aggregated_created')}
    ADD vendor_id int;

    ALTER TABLE {$installer->getTable('tax/tax_order_aggregated_created')}
    ADD vendor varchar(30);

    ALTER TABLE  {$installer->getTable('tax/tax_order_aggregated_created')} DROP INDEX  `FCA5E2C02689EB2641B30580D7AACF12` ,
    ADD UNIQUE  `FCA5E2C02689EB2641B30580D7AACF12` (  `period` ,  `store_id` ,  `code` ,  `percent` ,  `order_status` ,`vendor_id` );

    --
	-- ADD column `vendor_id`,`vendor` for  ` tax_order_aggregated_updated`   and change constraint of this table
	--

	ALTER TABLE {$installer->getTable('tax/tax_order_aggregated_updated')}
    ADD vendor_id int;

    ALTER TABLE {$installer->getTable('tax/tax_order_aggregated_updated')}
    ADD vendor varchar(30);

    ALTER TABLE  {$installer->getTable('tax/tax_order_aggregated_updated')} DROP INDEX  `DB0AF14011199AA6CD31D5078B90AA8D` ,
    ADD UNIQUE  `DB0AF14011199AA6CD31D5078B90AA8D` (  `period` ,  `store_id` ,  `code` ,  `percent` ,  `order_status` ,`vendor_id` );

    ALTER TABLE  `sales_bestsellers_aggregated_daily` ADD  `vendor_id` INT NULL;
	ALTER TABLE  `sales_bestsellers_aggregated_monthly` ADD  `vendor_id` INT NULL;
	ALTER TABLE  `sales_bestsellers_aggregated_yearly` ADD  `vendor_id` INT NULL;

    ALTER TABLE  `sales_shipping_aggregated` ADD  `vendor_id` INT NULL;
    ALTER TABLE  `sales_shipping_aggregated_order` ADD  `vendor_id` INT NULL;


");
$installer->endSetup();