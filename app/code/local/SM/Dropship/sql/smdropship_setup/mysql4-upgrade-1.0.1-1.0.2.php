<?php

$installer = $this;

$installer->startSetup();

$installer->run("
	ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_shipping_rate_free` text  NULL;
    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_shipping_rate_nofree` text NULL;
		
");

$installer->endSetup();
