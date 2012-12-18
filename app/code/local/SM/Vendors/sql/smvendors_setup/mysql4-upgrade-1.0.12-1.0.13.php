<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$vendor_order_table = $installer->getTable('smvendors/order');

$installer->run("
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `total_online_refunded` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `total_offline_refunded` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_total_online_refunded` DECIMAL(12,4) NOT NULL DEFAULT 0;
        ALTER TABLE `{$vendor_order_table}` ADD COLUMN `base_total_offline_refunded` DECIMAL(12,4) NOT NULL DEFAULT 0;
");

$installer->run("
		ALTER TABLE  {$this->getTable('smvendors/vendor')} ADD  `vendor_feature` BOOLEAN NULL , ADD  `vendor_contact_email` VARCHAR( 255 ) NULL
");

if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {
	$eavConfigModel = Mage::getSingleton('eav/config');
	$eavConfigModel->getAttribute('customer', 'customer_type')
		->setData('used_in_forms', array('adminhtml_customer', 'customer_account_create', 'customer_account_edit'))
		->save();
}

$installer->endSetup();