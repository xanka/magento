<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$installer->updateAttribute('catalog_product', 'sm_product_vendor_id', 
							array(
								'source_model' => 'smvendors/vendor_catalog_product_attribute_source_vendor',
								'frontend_input'=> 'select'
							)
			);
$installer->endSetup();
