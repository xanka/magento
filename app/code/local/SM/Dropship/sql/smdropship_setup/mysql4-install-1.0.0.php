<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('sales/quote_address_shipping_rate')} ADD `method_detail` TEXT NULL; 
ALTER TABLE {$this->getTable('sales/quote_address')} ADD `shipping_method_detail` TEXT NULL; 
ALTER TABLE {$this->getTable('sales/order')} ADD `shipping_method_detail` TEXT NULL; 

DROP TABLE IF EXISTS {$this->getTable('smdropship/vendor_shippping_flatrate')};
CREATE TABLE IF NOT EXISTS {$this->getTable('smdropship/vendor_shippping_flatrate')} (
  `config_id` int(11) NOT NULL auto_increment,
  `price` decimal(12,4) NOT NULL,
  `active` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  PRIMARY KEY  (`config_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

DROP TABLE IF EXISTS {$this->getTable('smdropship/vendor_shippping_orderrate')};
CREATE TABLE IF NOT EXISTS {$this->getTable('smdropship/vendor_shippping_orderrate')} (
  `config_id` int(11) NOT NULL auto_increment,
  `rates` text,
  `active` tinyint(1) NOT NULL default '0',
  `vendor_id` int(11) NOT NULL,
  PRIMARY KEY  (`config_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;
");

$installer->endSetup();
