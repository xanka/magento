<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$installer->run("
	--
	-- Table structure for table `sm_vendor_customer`
	--
	DROP TABLE IF EXISTS {$installer->getTable('smvendors/vendor_customer')};
	CREATE TABLE IF NOT EXISTS {$installer->getTable('smvendors/vendor_customer')} (
	  `id` int(11) NOT NULL auto_increment,
	  `vendor_id` int(11) NOT NULL,
	  `customer_id` int(11) NOT NULL,
	  PRIMARY KEY  (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");
$installer->endSetup();