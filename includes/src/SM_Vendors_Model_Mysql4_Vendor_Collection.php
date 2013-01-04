<?php
class SM_Vendors_Model_Mysql4_Vendor_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct() 
    {
        parent::_construct();
        $this->_init('smvendors/vendor');
    }
	
	public function toOptionArray()
	{
		$result= array();
        $result[] =  array('label'=>'All Vendor','value'=>'');
		foreach($this as $item){
			$result[] = array('label'=>$item->getVendorName(),'value'=>$item->getId());
		}
		return $result;
	}

    public function getOptionArray(){
        $result= array();
        foreach($this as $item){
            $result[$item->getId()] = $item->getVendorName();
        }
        return $result;
    }
	
	/**
	 * 
	 * @return SM_Vendors_Model_Mysql4_Vendor_Collection
	 */
	public function addActiveVendorFilter()
	{
		$this->addFieldToFilter('vendor_status', '1');
		return $this;
	}

    /**
     *
     * @return SM_Vendors_Model_Mysql4_Vendor_Collection
     */
    public function addDisplayToFilter()
    {
        $this->addFieldToFilter('vendor_displayed', '1');
        return $this;
    }
}