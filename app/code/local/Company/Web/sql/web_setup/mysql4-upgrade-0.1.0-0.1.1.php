<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$installer->run("
	--
	-- Table structure for table `question`
	--
	DROP TABLE IF EXISTS {$installer->getTable('web/question')};
	CREATE TABLE IF NOT EXISTS {$installer->getTable('web/question')} (
	  `id` int(11) NOT NULL auto_increment,
	  `question_type` varchar(100) NOT NULL,
	  `question` text(10000) NULL,
	  `answer` text(10000) NULL,
	  `status` boolean  NOT NULL,
	  PRIMARY KEY  (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

	--
	-- Table structure for table `answer`
	--
	DROP TABLE IF EXISTS {$installer->getTable('web/answer')};
	CREATE TABLE IF NOT EXISTS {$installer->getTable('web/answer')} (
	  `id` int(11) NOT NULL auto_increment,
	  `customer_id` int(11) NOT NULL,
	  `customer` varchar(100) NOT NULL,
	  `question_id` int(11) REFERENCES question(id),
	  `question` varchar(1000) NOT NULL,
	  `answer` text(10000) NULL,
	  PRIMARY KEY  (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");
$installer->endSetup();