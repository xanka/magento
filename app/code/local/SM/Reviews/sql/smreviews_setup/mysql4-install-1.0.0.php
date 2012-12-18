<?php

$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('smreviews/reviews')};
CREATE TABLE IF NOT EXISTS {$this->getTable('smreviews/reviews')} (
  `review_id` int(11) NOT NULL auto_increment,
  `comment` TEXT DEFAULT NULL,
  `rating` smallint (5) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` smallint (5) unsigned NOT NULL default '1',
  `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`review_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
");

$installer->endSetup();
