<?php

$resources[0] = '__root__';
$resources[1] = 'admin/catalog';
$resources[2] = 'admin/catalog/tag';
$resources[3] = 'admin/catalog/tag/all';
$resources[4] = 'admin/catalog/tag/pending';
$resources[5] = 'admin/catalog/sitemap';
$resources[6] = 'admin/catalog/search';
$resources[7] = 'admin/catalog/urlrewrite';
$resources[8] = 'admin/catalog/categories';
$resources[9] = 'admin/catalog/products';
$resources[10] = 'admin/customer';
$resources[11] = 'admin/customer/group';
$resources[12] = 'admin/customer/manage';
//$resources[13] = 'admin/customer/online';
$resources[14] = 'admin/smvendors';
$resources[15] = 'admin/smvendors/vendors_page';
$resources[16] = 'admin/smvendors/vendors_banner';
$resources[17] = 'admin/smvendors/vendors_profile';
$resources[18] = 'admin/smvendors/vendors_orders';
$resources[19] = 'admin/smvendors/vendors_orders/actions/create';
$resources[20] = 'admin/smvendors/vendors_orders/actions/view';
$resources[21] = 'admin/smvendors/vendors_orders/actions/email';
$resources[22] = 'admin/smvendors/vendors_orders/actions/reorder';
$resources[23] = 'admin/smvendors/vendors_orders/actions/edit';
$resources[24] = 'admin/smvendors/vendors_orders/actions/cancel';
$resources[25] = 'admin/smvendors/vendors_orders/actions/review_payment';
$resources[26] = 'admin/smvendors/vendors_orders/actions/capture';
$resources[27] = 'admin/smvendors/vendors_orders/actions/invoice';
$resources[28] = 'admin/smvendors/vendors_orders/actions/creditmemo';
$resources[29] = 'admin/smvendors/vendors_orders/actions/ship';
$resources[30] = 'admin/smvendors/vendors_orders/actions/comment';
$resources[31] = 'admin/smvendors/vendors_orders/actions/emails';
$resources[32] = 'admin/smvendors/vendors_orders/actions';
$resources[33] = 'admin/smvendors/vendors_invoices';
$resources[34] = 'admin/smvendors/vendors_shipments';
$resources[35] = 'admin/smvendors/vendors_creditmemos';
$resources[36] = 'admin/promo';
$resources[37] = 'admin/promo/catalog';
$resources[38] = 'admin/promo/quote';
$resources[39] = 'admin/report';
$resources[40] = 'admin/report/smvendors';
$resources[41] = 'admin/report/smvendors/vendororders';
$resources[42] = 'admin/dashboard';
// create new role for vendors
$role = Mage::getModel('admin/roles');
$role->setName('Vendors')
        ->setRoleType('G');
Mage::dispatchEvent('admin_permissions_role_prepare_save', array('object' => $role, 'request' => Mage::app()->getRequest()));
$role->save();

Mage::getModel("admin/rules")
        ->setRoleId($role->getId())
        ->setResources($resources)
        ->saveRel();


// save config values
$config = new Mage_Core_Model_Config();
$config->saveConfig('smvendors/roles/vendor', $role->getId(), 'default', 0);
