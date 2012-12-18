<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$installer->run("
	--
	-- Table structure for table `planet_partner`
	--
	DROP TABLE IF EXISTS {$installer->getTable('planet/partner')};
	CREATE TABLE IF NOT EXISTS {$installer->getTable('planet/partner')} (
	  `id` int(11) NOT NULL auto_increment,
	  `company_name` varchar(100) NOT NULL,
	  `web_site` varchar(100) NOT NULL,
	  `image_url` varchar(100) NOT NULL,
	  PRIMARY KEY  (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");
$installer->endSetup();