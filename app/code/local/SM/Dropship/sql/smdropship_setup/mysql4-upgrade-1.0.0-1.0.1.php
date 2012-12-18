<?php

$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('smdropship/vendor_shippping_multi_flat_rate')} (
  `rate_id` int(11) NOT NULL auto_increment,
  `title` text,
  `active` tinyint(1) NOT NULL default '0',
  `price` int(11) NOT NULL DEFAULT 0,
  `vendor_ids` TEXT NULL,
  `type` VARCHAR( 255 ) NOT NULL ,
  `description` TEXT NULL,
  `order_amount_limit` DOUBLE( 12, 4 ) NOT NULL DEFAULT 0,
  PRIMARY KEY  (`rate_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		
");

$installer->endSetup();
