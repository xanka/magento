<?php
class SM_Vendors_Block_Override_Adminhtml_Customer_Grid_Column_Renderer_CustomGroup extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
    	
    	$rowData = $row->getData('vendor_customer_group');
    	$groupIds = explode(',', $rowData);
    	$customGroupCollection = Mage::getResourceModel('customer/group_collection');
    	$customGroupCollection->addFieldToFilter('customer_group_id', array('in'=> $groupIds));
    	if($vendor = Mage::helper('smvendors')->getVendorLogin()){
    		$customGroupCollection->addFieldToFilter('vendor_id', $vendor->getId());
    	}
    	else{
    		$customGroupCollection->addFieldToFilter('vendor_id',array('neq' => 0));
    	}
    	$html = array();
    	foreach($customGroupCollection as $group){
    		$html[] = $group->getCustomerGroupCode();
    	}
        return implode("<br/>", $html);
        
    }
}