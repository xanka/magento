<?php
class SM_Vendors_Model_Override_Customer_Resource_Group_Collection extends Mage_Customer_Model_Resource_Group_Collection
{
    /**
     * Set real groups filter
     *
     * @return Mage_Customer_Model_Resource_Group_Collection
     */
    public function setRealGroupsFilter()
    {
        parent::setRealGroupsFilter();
        return $this->addFieldToFilter('vendor_id', 0);
    }
}
