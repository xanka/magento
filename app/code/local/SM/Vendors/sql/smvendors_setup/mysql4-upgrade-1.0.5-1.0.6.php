<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$setup = Mage::getModel('customer/entity_setup', 'core_setup');

$setup->addAttribute('customer', 'vendor_customer_group', array(
        'label'	 	 	=> 'Vendor custom group',
        'type'	 	 	=> 'text',
        'input'	 	 	=> 'multiselect',
        'global'	 	=> 1,
        'visible'	 	=> 1,
        'required'	 	=> 0,
        'user_defined'	=> 0,
        'position'		=> 200,
        'sort_order' 	=> 200,
        'source'		=> 'smvendors/customer_attribute_source_customergroup',
));

if (version_compare(Mage::getVersion(), '1.6.0', '<=')) {
    $customer = Mage::getModel('customer/customer');
    $attrSetId = $customer->getResource()->getEntityType()->getDefaultAttributeSetId();
    $setup->addAttributeToSet('customer', $attrSetId, 'General', 'vendor_customer_group');
}

if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {
    $eavConfigModel = Mage::getSingleton('eav/config');
    $eavConfigModel->getAttribute('customer', 'vendor_customer_group')
    ->setData('used_in_forms', array('adminhtml_customer'))
    ->save();
}

$installer->endSetup();