<?php
class SM_Vendors_Block_Override_Adminhtml_Promo_Catalog_Edit_Tab_Main
    extends Mage_Adminhtml_Block_Promo_Catalog_Edit_Tab_Main
{
    protected function _prepareForm()
    {
        parent::_prepareForm();
        
        $vendorId = Mage::registry('current_promo_catalog_rule')->getData('vendor_id');
        
        if(($vendor = Mage::helper('smvendors')->getVendorLogin()) || $vendorId) {
            if(!$vendorId) {
                $vendorId = $vendor->getId();
            }
            
            $customerGroups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('vendor_id', array($vendorId, 0))
            ->load()->toOptionArray();
            
            $found = false;
            foreach ($customerGroups as $group) {
                if ($group['value']==0) {
                    $found = true;
                }
            }
            if (!$found) {
                array_unshift($customerGroups, array('value'=>0, 'label'=>Mage::helper('catalogrule')->__('NOT LOGGED IN')));
            }        
            
            $fieldset = $this->getForm()->getElement('base_fieldset');
            $customerGroupsElement = $fieldset->getElements()->searchById('customer_group_ids');
            
            $customerGroupsElement->setValues($customerGroups);
        }
        
        return $this;
    }
}
