<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
		ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_slug` varchar(50) DEFAULT NULL AFTER `vendor_name`;
		ALTER TABLE {$this->getTable('smvendors/vendor')} ADD UNIQUE KEY `vendor_slug` (`vendor_slug`); 
");

$installer->endSetup();