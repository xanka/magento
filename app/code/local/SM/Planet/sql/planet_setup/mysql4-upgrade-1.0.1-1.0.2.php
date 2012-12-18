<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$installer->run("
	ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_bankname` varchar(250) NULL;
    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_reg_number` varchar(250) NULL;
    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_company_type` varchar(250) NULL;
    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_address` varchar(250) NULL;
    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_town` varchar(250) NULL;
    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_postcode` varchar(250) NULL;
    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_country` varchar(250) NULL;
    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_customer_services_email` varchar(250) NULL;
    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_customer_services_telephone` varchar(250) NULL;
    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_account_name` varchar(250) NULL;
    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_sort_code1` int(11) NULL;
    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_sort_code2` int(11) NULL;
    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_sort_code3` int(11) NULL;
    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_swift` varchar(50) NULL;
    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_iban` varchar(50) NULL;
    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_is_vendor_vegan_society_approve` tinyint(4) DEFAULT '0' NULL;
    ALTER TABLE {$this->getTable('smvendors/vendor')} ADD COLUMN `vendor_is_subscribed_other` tinyint(4) DEFAULT '0' NULL;
");
$installer->endSetup();