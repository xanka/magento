<?php
class SM_Vendors_Model_Customer_Attribute_Source_Customergroup extends Mage_Eav_Model_Entity_Attribute_Source_Table
{
    public function getAllOptions()
    {
        if(!$this->_options) {
            $collection = Mage::getResourceModel('customer/group_collection');
            $collection->addFieldToFilter('vendor_id', array('gt' => 0));
            
            if($vendor = Mage::helper('smvendors')->getVendorLogin()) {
                $collection->addFilter('vendor_id', $vendor->getId());
            }

            $this->_options = $collection->load()->toOptionArray();
            array_unshift($this->_options, array('value'=> '', 'label'=>''));
        }
        return $this->_options;
    }
}
