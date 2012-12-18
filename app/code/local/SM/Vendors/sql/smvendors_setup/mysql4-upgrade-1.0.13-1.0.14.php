<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
	DROP TABLE IF EXISTS {$this->getTable('smvendors/banner')};
	CREATE TABLE IF NOT EXISTS {$this->getTable('smvendors/banner')} (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `vendor_id` int(11) NOT NULL,
	  `title` text CHARACTER SET utf8,
	  `image` varchar(255) CHARACTER SET utf8,
	  `active` tinyint(1) NOT NULL DEFAULT '0',
	  `position` varchar(255) NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");

$installer->endSetup();