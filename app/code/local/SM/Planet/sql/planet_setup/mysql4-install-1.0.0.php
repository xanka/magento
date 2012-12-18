<?php
$installer = $this;
$installer->startSetup();
$setup = Mage::getModel('catalog/resource_setup','core_setup');
$setup->addAttribute('catalog_product','is_featured' , array(
    'label' => 'Featured' ,
    'type' => 'text' ,
    'input' => 'select' ,
    'global' => 1,
    'visible' => 1,
    'required' => 0 ,
    'user_defined' =>0 ,
    'position' => 200 ,
    'sort_order' => 200 ,
    'source' => 'planet/source_featured'
));

$setup->addAttribute('catalog_product','featured_valid_from' , array(
    'label' => 'Featured Valid From' ,
    'type' => 'datetime' ,
    'input' => 'date' ,
    'global' => 1,
    'visible' => 1,
    'required' => 0 ,
    'user_defined' =>0 ,
    'position' => 201 ,
    'sort_order' => 201 ,
    'backend' => 'eav/entity_attribute_backend_datetime'

));

$setup->addAttribute('catalog_product','featured_valid_to' , array(
    'label' => 'Featured Valid To' ,
    'type' => 'datetime' ,
    'input' => 'date' ,
    'global' => 1,
    'visible' => 1,
    'required' => 0 ,
    'user_defined' =>0 ,
    'position' => 202 ,
    'sort_order' => 202 ,
    'backend' => 'eav/entity_attribute_backend_datetime'
));

$setup->addAttribute('customer','is_featured' , array(
    'label' => 'Featured' ,
    'type' => 'text' ,
    'input' => 'select' ,
    'global' => 1,
    'visible' => 1,
    'required' => 0 ,
    'user_defined' =>0 ,
    'position' => 200 ,
    'sort_order' => 200 ,
    'source' => 'planet/source_featured'
));

$setup->addAttribute('customer','featured_valid_from' , array(
    'label' => 'Featured Valid From' ,
    'type' => 'datetime' ,
    'input' => 'date' ,
    'global' => 1,
    'visible' => 1,
    'required' => 0 ,
    'user_defined' =>0 ,
    'position' => 201 ,
    'sort_order' => 201 ,
    'backend' => 'eav/entity_attribute_backend_datetime'

));

$setup->addAttribute('customer','featured_valid_to' , array(
    'label' => 'Featured Valid To' ,
    'type' => 'datetime' ,
    'input' => 'date' ,
    'global' => 1,
    'visible' => 1,
    'required' => 0 ,
    'user_defined' =>0 ,
    'position' => 202 ,
    'sort_order' => 202 ,
    'backend' => 'eav/entity_attribute_backend_datetime'
));

$setup->addAttribute('customer','filterproducbyareat' , array(
    'label' => 'Filter product display by vendor delivery area' ,
    'type' => 'text' ,
    'input' => 'select' ,
    'global' => 1,
    'visible' => 1,
    'required' => 0 ,
    'user_defined' =>1 ,
    'position' => 203 ,
    'sort_order' => 203 ,
    'source' => 'planet/source_featured'
));




$installer->endSetup();
?>