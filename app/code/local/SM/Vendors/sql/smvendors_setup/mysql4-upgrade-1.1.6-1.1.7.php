<?php
/**
 * Author : Nguyen Trung Hieu
 * Email : hieunt@smartosc.com
 * Date: 8/29/12
 * Time: 1:18 PM
 */

$installer = $this;

$installer->startSetup();
$installer->run("
	--
	-- Table structure for table `sm_vendor_customer`
	--
	ALTER TABLE sm_vendor
    ADD description varchar(9999);
");
$installer->endSetup();