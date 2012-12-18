<?php
class SM_Vendors_Model_Customer_Attribute_Source_Type extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    public function getAllOptions()
    {
    	$options = array(
			array(
				'value'=> 'vendor', 
				'label'=> Mage::helper('smvendors')->__('Vendor')
			),
			array(
				'value'=> 'buyer', 
				'label'=> Mage::helper('smvendors')->__('Buyer')
			),
		);
        return $options;
    }
}
