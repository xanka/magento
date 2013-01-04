<?php

class SM_Vendors_Helper_Form extends Mage_Core_Helper_Abstract
{
    public function removeFieldOptions($field, $removed = array())
    {
        $options = $field->getOptions();
        $values = $field->getValues();
        foreach ($removed as $optionId) {
            unset($options[$optionId]);
        }
        
        foreach ($values as $key => $value) {
            if(in_array($value['value'], $removed)) {
                unset($values[$key]);
            }
        }
        
        $field->setOptions($options);
        $field->setValues($values);        
    }
    
    public function turnSelectToHidden($actionFieldset, $eleId, $value)
    {
        $actionFieldset->getElements()->remove($eleId);
        $actionFieldset->getForm()->removeField($eleId);
        $this->addHiddenField($actionFieldset, $eleId, $value);
    }
    
    public function addHiddenField($actionFieldset, $eleId, $value)
    {
        $actionFieldset->addField($eleId, 'hidden', array(
                'name'      => $eleId,
                'value'     => $value,
        ));        
    }
}