<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$installer->run("
        ALTER TABLE `{$installer->getTable('salesrule/rule')}` ADD `vendor_id` INT NOT NULL
");

$installer->run("
        ALTER TABLE `{$installer->getTable('catalogrule/rule')}` ADD `vendor_id` INT NOT NULL
");

$installer->run("
        ALTER TABLE `{$installer->getTable('customer/customer_group')}` ADD `vendor_id` INT NOT NULL 
");

$installer->endSetup();