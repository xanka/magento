<?php
class SM_Vendors_Model_Vendor_Catalog_Product_Attribute_Source_Vendor extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    public function getAllOptions()
    {
    	$options = array();
		$collection = Mage::getModel('smvendors/vendor')->getCollection()->addActiveVendorFilter();
		foreach($collection as $vendor){
			
				$options[] = array(
					'value' => $vendor->getId(),
					'label' => $vendor->getVendorName()
				);
		}
        return $options;
    }
    
	public function getOptionText($value)
    {
    	
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if(is_array($value)){
                if (in_array($option['value'],$value)) {
                    return $option['label'];
                }
            }
            else{
                if ($option['value']==$value) {
                    return $option['label'];
                }
            }
 
        }
        return false;
    }
    
}
