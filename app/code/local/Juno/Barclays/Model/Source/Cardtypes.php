<?php
/**
 *
 *
 *
 */
class Juno_Barclays_Model_Source_Cardtypes
{
    public function toOptionArray()
    {
		//TODO the preauth
		 return array(
            array('value' => 1, 'label' => Mage::helper('barclays')->__('Electron')),
            array('value' => 2, 'label' => Mage::helper('barclays')->__('American Express')),
            array('value' => 8, 'label' => Mage::helper('barclays')->__('Maestro')),
            array('value' => 16, 'label' => Mage::helper('barclays')->__('JCB')),
            array('value' => 32, 'label' => Mage::helper('barclays')->__('MasterCard')),
            array('value' => 64, 'label' => Mage::helper('barclays')->__('Visa'))
        );
    }
}