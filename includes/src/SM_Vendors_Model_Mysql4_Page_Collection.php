<?php
class SM_Vendors_Model_Mysql4_Page_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('smvendors/page');
    }
	public function addVendorToSelect(){
		$this->getSelect()
			->join(array('v' => $this->getTable('smvendors/vendor')), 'main_table.vendor_id = v.vendor_id', array('vendor_name'=>'vendor_name'));
		return $this;
	}
	
	public function addVendorToFilter($vendorId){
		$this->getSelect()
			->where('main_table.vendor_id=?',$vendorId);
		return $this;
	}
}