<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$installer->run("
	
	DROP TABLE IF EXISTS {$installer->getTable('smvendors/order')};
	CREATE TABLE IF NOT EXISTS {$installer->getTable('smvendors/order')} (
	  `entity_id` int(11) NOT NULL AUTO_INCREMENT,
	  `order_id` int(11) NOT NULL,
	  `vendor_id` int(11) NOT NULL,
	  `increment_id` varchar(50) NOT NULL,
	  `status` varchar(32) NOT NULL,
	  `subtotal` decimal(12,4) DEFAULT '0',
	  `tax_amount` decimal(12,4) DEFAULT '0',
	  `shipping_amount` decimal(12,4) DEFAULT '0',
	  `shipping_tax_amount` decimal(12,4) DEFAULT '0',
	  `shipping_method` varchar(50) DEFAULT '',
	  `shipping_carrier` varchar(50) DEFAULT '',
	  `shipping_method_title` varchar(50) DEFAULT '',
	  `shipping_carrier_title` varchar(50) DEFAULT '',
	  `discount_amount` decimal(12,4) DEFAULT '0',
	  `discount_description` varchar(255) DEFAULT '',
	  `grand_total` decimal(12,4) DEFAULT '0',
	  `commission_amount` decimal(12,4) DEFAULT '0',
	  PRIMARY KEY (`entity_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

	-- --------------------------------------------------------
");
$installer->endSetup();