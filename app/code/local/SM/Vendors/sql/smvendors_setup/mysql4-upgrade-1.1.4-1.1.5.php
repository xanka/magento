<?php
/**
 * Author : Nguyen Trung Hieu
 * Email : hieunt@smartosc.com
 * Date: 8/29/12
 * Time: 1:18 PM
 */

$installer = $this;

$installer->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$setup->addAttribute('customer', 'fullname', array(
    'input'         => 'text',
    'type'          => 'text',
    'label'         => 'Full Name',
    'visible'       => 1,
    'required'      => 1,
    'user_defined'  => 1,
));

$setup->addAttributeToGroup(
    $entityTypeId,
    $attributeSetId,
    $attributeGroupId,
    'fullname',
    '999'  //sort_order
);


$oAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'fullname');
$oAttribute->setData('used_in_forms', array('adminhtml_customer'));

$oAttribute->save();

$installer->endSetup();