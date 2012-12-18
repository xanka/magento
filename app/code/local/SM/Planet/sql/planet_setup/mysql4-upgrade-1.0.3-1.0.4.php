<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$installer->run("

    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `is_vendor_vegan_society` tinyint(4) DEFAULT '0' NULL;

");
$installer->endSetup();