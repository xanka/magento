<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$installer->run("

    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_account_number` varchar(250) NULL;

");
$installer->endSetup();