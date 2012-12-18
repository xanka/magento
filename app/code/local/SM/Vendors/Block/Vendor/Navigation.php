<?php
/**
 * @category    SM
 * @package     SM_Vendors
 * @copyright   Copyright (c) 2012 SmartOSC (www.smartosc.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SM_Vendors_Block_Vendor_Navigation extends Mage_Core_Block_Template
{
	/**
	 * 
	 * @var int
	 */
	const DEFAULT_TOTAL_COLUMNS = 4;
	
	/**
	 * 
	 * @var array
	 */
	protected $_vendorGroupsColumns = null;
	
	/**
	 * 
	 * @return int
	 */
	public function getTotalColumns()
	{
		return $this->getData('total_columns') ? (int) $this->getData('total_columns') : self::DEFAULT_TOTAL_COLUMNS;
	}
	
	/**
	 * Get sorted vendors grouped by first character
	 * 
	 * @return array
	 */
	public function getSortedVendorGroupsColumns()
	{
		if (is_null($this->_vendorGroupsColumns)) {
			$vendors = Mage::getResourceModel('smvendors/vendor_collection')
				->addActiveVendorFilter();
			
			$vendorGroups = array();
			
			foreach ($vendors as $vendor) {
				$vendorName = $vendor->getVendorName();
				$firstChar = strtolower(substr($vendorName, 0, 1));
				$firstCharCode = ord($firstChar);
			
				// check character code of first character (a-z) = (97-122)
				if ($firstCharCode >= 97 && $firstCharCode <= 122) {
					$groupName = $firstChar;
				} else {
					$groupName = '#';
				}
			
				if (!isset($vendorGroups[$groupName])) {
					$vendorGroups[$groupName] = array();
				}

				$vendorGroups[$groupName][strtolower($vendorName)] = $vendor;
			}

			// sort vendor groups
			foreach ($vendorGroups as &$vendorGroup) {
				ksort($vendorGroup);
			}
			
			ksort($vendorGroups);
			
			// distribute groups into columns
			$totalGroups = sizeof($vendorGroups);
			$totalColumns = $this->getTotalColumns();
			$col = 0;
			$countGroupsInColumn = 0;
			$standardGroupsInColumn = (int) ($totalGroups / $totalColumns); 
			$redundantGroups = $totalGroups % $totalColumns; 
			
			foreach ($vendorGroups as $groupName => $group) {
				$countGroupsInColumn++;
				
				if ($countGroupsInColumn > ($standardGroupsInColumn + 1 * ($col < $redundantGroups))) {
					$countGroupsInColumn = 1;
					$col++;
				}

				$this->_vendorGroupsColumns[$col][$groupName] = $group;
			}
		}
		
		return $this->_vendorGroupsColumns;
	}

	/**
	 * 
	 * @param int $vendorId
	 */
	public function getProductCountByVendor($vendorId)
	{
		return 1;
	}
}