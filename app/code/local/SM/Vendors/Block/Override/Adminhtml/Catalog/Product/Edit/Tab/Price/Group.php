<?php
class SM_Vendors_Block_Override_Adminhtml_Catalog_Product_Edit_Tab_Price_Group
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Price_Group
{
    /**
     * Retrieve allowed customer groups
     *
     * @param int $groupId  return name by customer group id
     * @return array|string
     */
    public function getCustomerGroups($groupId = null)
    {
        if ($this->_customerGroups === null) {
            if (!Mage::helper('catalog')->isModuleEnabled('Mage_Customer')) {
                return array();
            }
            $collection = Mage::getModel('customer/group')->getCollection();
            
//             /*** 2012-04-18 HiepHM Exclude vendor specific group START ***/
//             $collection->addFieldToFilter('vendor_id', 0);
//             /*** 2012-04-18 HiepHM Exclude vendor specific group END ***/
            /*** 2012-05-08 HiepHM Include only vendor specific group START ***/
            if($vendor = Mage::helper('smvendors')->getVendorLogin()) {
                $collection->addFieldToFilter('vendor_id', $vendor->getId());
            }
            /*** 2012-05-08 HiepHM Include only vendor specific group END ***/
            
            $this->_customerGroups = array();

            foreach ($collection as $item) {
                /* @var $item Mage_Customer_Model_Group */
                $this->_customerGroups[$item->getId()] = $item->getCustomerGroupCode();
            }
        }

        if ($groupId !== null) {
            return isset($this->_customerGroups[$groupId]) ? $this->_customerGroups[$groupId] : array();
        }

        return $this->_customerGroups;
    } 
}
