<?php
/**
 *
 *
 *
 */
class Juno_Barclays_Model_Source_ChargeType
{
    public function toOptionArray()
    {
		//TODO the preauth
		 return array(
            array('value' =>Juno_Barclays_Model_Epdq::PAYMENT_CHARGE_TYPE_AUTH, 'label' => Mage::helper('barclays')->__('Authorize'))
        );
    }
}