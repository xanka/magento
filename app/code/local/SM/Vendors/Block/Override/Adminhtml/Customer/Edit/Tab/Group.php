<?php
class SM_Vendors_Block_Override_Adminhtml_Customer_Edit_Tab_Group extends Mage_Adminhtml_Block_Customer_Edit_Tab_Account
{
    public function initForm()
    {
    	$customer = Mage::registry('current_customer');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_account');
        $form->setFieldNameSuffix('account');
        $customerForm = Mage::getModel('customer/form');
        $customerForm->setEntity($customer)
            ->setFormCode('adminhtml_customer')
            ->initDefaultValues();

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend'=>Mage::helper('customer')->__('Account Information'))
        );
        
        $form->setValues($customer->getData());
        $this->setForm($form);
        
        $currentGroup = $customer->getData('vendor_customer_group');

        
        
        if($currentGroup) {
            $currentGroup = explode(',', $currentGroup);
            // Remove blank value
            foreach ($currentGroup as $key => $item) {
                if(!$item) {
                    unset($currentGroup[$key]);
                }
            }
        }
        
        if($vendor = Mage::helper('smvendors')->getVendorLogin()) {
            // Move other vendor's groups to hidden field.
            $groupOptions = Mage::getSingleton('eav/config')
                ->getAttribute('customer', 'vendor_customer_group')
                ->getSource()->getAllOptions();
            
            $vendorGroups = array();
            
            foreach ($groupOptions as $option) {
                if(in_array($option['value'], $currentGroup)) {
                    $vendorGroups[] = $option['value'];
                }
            }
            
            $hiddenGroups = array_diff($currentGroup, $vendorGroups);
            $currentGroup = $vendorGroups;
            
            $fieldset->addField('vendor_customer_group', 'multiselect',
                array(
                    'name'  => 'vendor_customer_group',
                    'values' => $groupOptions
                )
            );
         	
            $fieldset->addField('vendor_customer_group_hidden', 'hidden',
                array(
                    'name'  => 'vendor_customer_group[]',
                    'value' => implode(',', $hiddenGroups),
                )
            );

         
        }
        
        $values = $this->getForm()->addValues(array('vendor_customer_group' => implode(',', $currentGroup)));
    	
        
        
        
        return $this;
    }
}
