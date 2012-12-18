<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$vendor_banner_table = $installer->getTable('smvendors/banner');

$installer->run("
        ALTER TABLE `{$vendor_banner_table}` ADD COLUMN `width` VARCHAR(255) NULL DEFAULT 0, ADD COLUMN `height` VARCHAR(255) NULL DEFAULT 0;
");

$installer->endSetup();