<?php
class SM_Vendors_Block_Override_Checkout_Cart extends Mage_Checkout_Block_Cart
{

	
	/**
	 * 
	 * @var Array
	 */
	protected $_vendorItems;
	
	/**
	 * 
	 * @return Array
	 */
	public function getVendorItems()
	{
		$items = $this->getItems();

		if (empty($this->_vendorItems)) {
			$this->_vendorItems = array();
			foreach ($items as $item) {
				if(empty($this->_vendorItems[$item->getVendorId()])){
					$this->_vendorItems[$item->getVendorId()] = array();
				}
				$this->_vendorItems[$item->getVendorId()][] = $item;
			}
		}
		
		return $this->_vendorItems;
	}

	/**
	 * 
	 * @param int $vendorId
	 * @return SM_Vendors_Model_Vendor
	 */
	public function getVendor($vendorId)
	{
		$vendor = Mage::getModel('smvendors/vendor')->load($vendorId);
		return $vendor;
	}

	/**
	 * Add renderer for item product type
	 *
	 * @param   string $productType
	 * @return  SM_Vendors_Block_Override_Checkout_Cart
	 */
	public function removeItemRender($productType)
	{
		unset($this->_itemRenders[$productType]);
		
		return $this;
	}
}
