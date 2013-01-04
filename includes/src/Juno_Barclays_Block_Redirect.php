<?php
/**
 *
 *
 *
 */
class Juno_Barclays_Block_Redirect extends Mage_Core_Block_Template
{
	/**
     * Set template.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('barclays/redirect.phtml');
    }

}