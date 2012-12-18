<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('smreviews/reviews')} ADD COLUMN `vendor_reply` text NULL;
");

$installer->endSetup();
