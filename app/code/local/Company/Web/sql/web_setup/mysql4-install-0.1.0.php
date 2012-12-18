<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('web/web')};
CREATE TABLE {$this->getTable('web/web')} (
  `web_id` int(11) unsigned NOT NULL auto_increment,
  `company` varchar(255) NOT NULL default '',
  `website` varchar(255) NOT NULL default '',
  `image_url` text NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL  ,
  PRIMARY KEY (`web_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 