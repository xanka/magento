<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
        ALTER TABLE  {$this->getTable('smvendors/vendor')} ADD  `vendor_total_invoices` INT(10) NOT NULL DEFAULT 0;
        ALTER TABLE  {$this->getTable('smvendors/vendor')} ADD  `vendor_total_shipments` INT(10) NOT NULL DEFAULT 0;
        ALTER TABLE  {$this->getTable('smvendors/vendor')} ADD  `vendor_total_creditmemos` INT(10) NOT NULL DEFAULT 0;
");

$installer->endSetup();