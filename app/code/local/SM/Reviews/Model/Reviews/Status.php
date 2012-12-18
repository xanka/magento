<?php

class SM_Reviews_Model_Reviews_Status
{
    const STATUS_ENABLED    = 1;
    const STATUS_DISABLED   = 2;

    /**
     * Retrieve option array
     *
     * @return array
     */
    static public function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED    => Mage::helper('smreviews')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('smreviews')->__('Disabled')
        );
    }
}
