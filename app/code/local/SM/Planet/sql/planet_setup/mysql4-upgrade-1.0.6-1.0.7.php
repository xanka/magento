<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$installer->run("
    ALTER TABLE `sm_vendor`
    DROP `vendor_sort_code1`,
    DROP `vendor_sort_code2`,
    DROP `vendor_sort_code3`;


    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_sort_code1` varchar(2) NULL;
    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_sort_code2` varchar(2) NULL;
    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_sort_code3` varchar(2) NULL;
");
$installer->endSetup();