<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
//require_once('app/Mage.php');
Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
///** @noinspection PhpParamsInspection */
$installer = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();
$attribute  = array(
    'type'          => 'int',
    'backend_type'  => 'int',
    'frontend_input' => 'int',
    'is_user_defined' => true,
    'label'         => 'Has Send Email Request Review',
    'visible'       => true,
    'required'      => false,
    'user_defined'  => false,
    'searchable'    => false,
    'filterable'    => false,
    'comparable'    => false,
    'default'       => ''
);
$installer->addAttribute('order', 'hassendreview', $attribute);
$installer->endSetup();