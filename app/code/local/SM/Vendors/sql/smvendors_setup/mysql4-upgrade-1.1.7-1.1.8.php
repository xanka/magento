<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$installer->run("
	--
	-- Change default value of vendor_commision
	--
	ALTER TABLE  `sm_vendor` CHANGE  `vendor_commission`  `vendor_commission` DECIMAL( 12, 4 ) NOT NULL DEFAULT  '12'
");
$installer->endSetup();