<?php
class SM_Vendors_Model_Override_Eav_Entity_Type extends Mage_Eav_Model_Entity_Type
{
    /**
     * Retreive new incrementId
     *
     * @param int $storeId
     * @return string
     */
    public function fetchNewIncrementId($storeId = null)
    {
        $entityTypeCode = $this->getEntityTypeCode();
        switch ($entityTypeCode) {
            case 'vendor_order':
                // To get correct column name (vendor_total_orders)
                $entityTypeCode = 'order';
            case 'invoice':
            case 'creditmemo':
            case 'shipment':
                $vendor = Mage::helper('smvendors')->getVendorLogin();
                if(!$vendor) {
                    // Split order when creating from front-end.
                    $vendor = $this->getData('vendor_object');
                }
                if($vendor) {
                    $lastId = $vendor->getData("vendor_total_{$entityTypeCode}s");
                    $incrementId = $vendor->getVendorPrefix() . '-' . str_pad($lastId + 1, 9, 0, STR_PAD_LEFT);
                    $vendor->setData("vendor_total_{$entityTypeCode}s", $lastId + 1);
                    $vendor->save();
                    return $incrementId;
                } else {
                    return parent::fetchNewIncrementId($storeId);
                }
            default: return parent::fetchNewIncrementId($storeId);
        }
    }
}
