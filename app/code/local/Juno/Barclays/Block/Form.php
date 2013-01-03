<?php
/**
 *
 *
 *
 */
class Juno_Barclays_Block_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('barclays/form.phtml');
        parent::_construct();
    }
}