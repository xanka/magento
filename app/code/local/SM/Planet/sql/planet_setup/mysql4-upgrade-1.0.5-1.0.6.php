<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$installer->run("
ALTER TABLE  `sm_vendor` CHANGE  `description`  `description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
CHANGE  `return_page`  `return_page` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
CHANGE  `delivery_page`  `delivery_page` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL
");
$installer->endSetup();