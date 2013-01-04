<?php

class SM_Vendors_Block_Override_Adminhtml_Customer_Edit_Tab_Account extends Mage_Adminhtml_Block_Customer_Edit_Tab_Account {

    public function initForm() {

        $arr = array(
            'website_id',
            'prefix',
            'middlename',
            'suffix',
            'dob',
            'customer_type',
            'gender',
            'vendor_customer_group',
            'fullname'
        );
        parent::initForm();

        $currentGroup = Mage::registry('current_customer')->getData('vendor_customer_group');

        if ($currentGroup) {
            $currentGroup = explode(',', $currentGroup);
            // Remove blank value
            foreach ($currentGroup as $key => $item) {
                if (!$item) {
                    unset($currentGroup[$key]);
                }
            }
        }

        $this->removeRedundantFields();

        if ($vendor = Mage::helper('smvendors')->getVendorLogin()) {
            // Move other vendor's groups to hidden field.
            $groupOptions = Mage::getSingleton('eav/config')
                            ->getAttribute('customer', 'vendor_customer_group')
                            ->getSource()->getAllOptions();

            $vendorGroups = array();

            foreach ($groupOptions as $option) {
                if (in_array($option['value'], $currentGroup)) {
                    $vendorGroups[] = $option['value'];
                }
            }

            $hiddenGroups = array_diff($currentGroup, $vendorGroups);
            $currentGroup = $vendorGroups;

            $fieldset = $this->getForm()->getElement('base_fieldset');

            $fieldset->addField('vendor_customer_group_hidden', 'hidden', array(
                'name' => 'vendor_customer_group[]',
                'value' => implode(',', $hiddenGroups),
                    )
            );



            // disable field with vendor login
            foreach ($fieldset->getElements() as $element) {
                if ($this->isVendorElementNotAllow($element->getId())) {
                    //$element->setReadonly(true, true);

                    $fieldset->removeField($element->getId());
                }
            }

            //$this->getForm()->getElement('password_fieldset')->setReadonly(true, true);
        }

        $values = $this->getForm()->addValues(array('vendor_customer_group' => implode(',', $currentGroup)));

        return $this;
    }

    public function removeRedundantFields() {
        $arr = array(
            'website_id',
            'prefix',
            'middlename',
            'suffix',
            'dob',
            'customer_type',
            'gender',
            'vendor_customer_group',
            'fullname'
        );

        $fieldset = $this->getForm()->getElement('base_fieldset');

        foreach ($fieldset->getElements() as $element) {
            if (in_array($element->getData('name'), $arr)) {
                $fieldset->removeField($element->getId());
            }
        }

        $fieldset->addField('website_id', 'hidden', array(
            'name' => 'website_id',
            'value' => 1,
                )
        );

        $fieldset->addField('fullname', 'hidden', array(
            'name' => 'fullname',
            'value' => 1,
                )
        );

        $fieldset->addField('customer_type', 'hidden', array(
            'name' => 'customer_type',
            'value' => 'vendor',
                )
        );
    }

    protected function isVendorElementNotAllow($element) {
        $vendorElementAllows = array(
            'created_at',
            'website_id',
            'created_in',
            'group_id',
            'prefix',
            'suffix',
            'customer_type',
            'dob',
            'gender',
            'vendor_customer_group'
        );
        if ($vendor = Mage::helper('smvendors')->getVendorLogin()) {
            if (in_array($element, $vendorElementAllows)) {
                return true;
            }
        }
        return false;
    }

}
