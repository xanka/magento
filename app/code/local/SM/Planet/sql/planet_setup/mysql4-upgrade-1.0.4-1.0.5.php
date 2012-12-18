<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$installer->run("
    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `return_page` varchar(9999) DEFAULT '' NULL;
    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `delivery_page` varchar(9999) DEFAULT '' NULL;

");
$installer->endSetup();