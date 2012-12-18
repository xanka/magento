<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
        ALTER TABLE `{$installer->getTable('customer/customer_group')}` ADD `can_see_price` INT NOT NULL;
        ");

$installer->endSetup();