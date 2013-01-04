<?php
/**
 * Report order collection
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class SM_Vendors_Model_Mysql4_Reports_VendorOrders_CollectionAbstract extends Mage_Sales_Model_Mysql4_Report_Collection_Abstract
{
    protected $_type = 'created_at';
	protected $_periodFormat;
    protected $_selectedColumns = array();
	protected $_vendorIds = array();	
    /**
     * Initialize custom resource model
     */
    public function __construct()
    {
    	
		parent::_construct();
        $this->setModel('adminhtml/report_item');
        $this->_resource = Mage::getResourceModel('sales/report')->init('smvendors/order','entity_id');
        $this->setConnection($this->getResource()->getReadConnection());
		
    }

    protected function _getSelectedColumns()
    {
		if ('month' == $this->_period) {
            $this->_periodFormat = 'DATE_FORMAT(mo.'.$this->_type.', \'%Y-%m\')';
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = 'EXTRACT(YEAR FROM mo.'.$this->_type.')';
        } else {
            $this->_periodFormat = 'DATE(mo.'.$this->_type.')';
        }
		
		$this->setAggregatedColumns(array(
			'number_order' 			=> 'count(distinct(e.entity_id))',
			'grand_total'  		=> 'sum(e.grand_total)',
			'shipping_amount' 	=> 'sum(e.shipping_amount)',
			'currency_code'		=> 'mo.order_currency_code',
			'tax_amount'		=> 'sum(e.tax_amount)',
			'commission_amount' => 'sum(commission_amount)',
			'period'			=> $this->_periodFormat
		));
		
        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->_selectedColumns = array(
                'number_order'   		=> 'count(e.entity_id)',
            	'store_name'			=> 'mo.store_name',
				'store_id'				=> 'mo.store_id',
				'period'				=> $this->_periodFormat,
				'currency_code'			=> 'mo.order_currency_code',
				'grand_total'			=> 'sum(e.grand_total)',
				'shipping_amount'		=> 'sum(e.shipping_amount)',
				'tax_amount'			=> 'sum(e.tax_amount)',
            	'vendor_name'			=> 'v.vendor_name',
            	'vendor_prefix'			=> 'v.vendor_prefix',
            	'commission_amount'		=> 'sum(commission_amount)'
            );
        }

        if ($this->isTotals()) {
            $this->_selectedColumns = $this->getAggregatedColumns();
        }
        
    	if ($this->isSubTotals()) {
            $this->_selectedColumns = $this->getAggregatedColumns() + array('period' => $this->_periodFormat);
        }

        return $this->_selectedColumns;
    }

    /**
     * Add selected data
     *
     * @return Mage_Sales_Model_Mysql4_Report_Order_Collection
     */
	protected  function _initSelect()
    {
        if ($this->_inited) {
            return $this;
        }
		$columns = $this->_getSelectedColumns();
		//print_r($columns);die;
        $mainTable = $this->getResource()->getMainTable();
        $select = $this->getSelect()
            ->from(array('e' => $mainTable), $columns)
			->join(array('mo'=>$this->getTable('sales/order')),"mo.entity_id = e.order_id",array())
			->join(array('v'=>$this->getTable('smvendors/vendor')),"v.vendor_id = e.vendor_id",array());
			
		if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->getSelect()->group(array(
                $this->_periodFormat,'e.vendor_id'
            ));
        }
        if ($this->isSubTotals()) {
            $this->getSelect()->group(array(
                $this->_periodFormat
            ));
        }
        $this->_inited = true;
        return $this;
    }
	
	/**
     * Apply date range filter
     *
     * @return SM_AdvancedReports_Model_Mysql4_Reports_Ordersdelivery_Collection
     */
    protected function _applyDateRangeFilter()
    {
        if (!is_null($this->_from)) {
            $this->getSelect()->where('DATE(mo.'.$this->_type.') >= ?', $this->_from);
        }
        if (!is_null($this->_to)) {
            $this->getSelect()->where('DATE(mo.'.$this->_type.') <= ?', $this->_to);
        }
        return $this;
    }
	
	/**
     * Apply stores filter to select object
     *
     * @param Zend_Db_Select $select
     * @return Mage_Sales_Model_Mysql4_Report_Collection_Abstract
     */
    protected function _applyStoresFilter()
    {
        $nullCheck = false;
        $storeIds = $this->_storesIds;

        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }

        $storeIds = array_unique($storeIds);

        if ($index = array_search(null, $storeIds)) {
            unset($storeIds[$index]);
            $nullCheck = true;
        }

        if ($nullCheck) {
            $this->getSelect()->where('mo.store_id IN(?) OR mo.store_id IS NULL', $storeIds);
        } elseif ($storeIds[0] != '') {
            $this->getSelect()->where('mo.store_id IN(?)', $storeIds);
        }

        return $this;
    }
	

    /**
     * Apply order status filter
     *
     * @return Mage_Sales_Model_Mysql4_Report_Collection_Abstract
     */
    protected function _applyOrderStatusFilter()
    {
        if (is_null($this->_orderStatus)) {
            return $this;
        }
        $orderStatus = $this->_orderStatus;
        if (!is_array($orderStatus)) {
            $orderStatus = array($orderStatus);
        }
        $this->getSelect()->where('e.status IN(?)', $orderStatus);
        return $this;
    }
    
	/**
     * Apply vendor filter
     *
     * @return Mage_Sales_Model_Mysql4_Report_Collection_Abstract
     */
	protected function _applyVendorFilter()
    {
        $nullCheck = false;
        $vendorIds = $this->_vendorIds;

        if (!is_array($vendorIds)) {
            $vendorIds = array($vendorIds);
        }

        $vendorIds = array_unique($vendorIds);

        if ($index = array_search(null, $vendorIds)) {
            unset($vendorIds[$index]);
            $nullCheck = true;
        }
		if(!empty($vendorIds)){
	        if ($nullCheck) {
	            $this->getSelect()->where('e.vendor_id IN(?) OR e.vendor_id IS NULL', $vendorIds);
	        } elseif ($vendorIds[0] != '') {
	            $this->getSelect()->where('e.vendor_id IN(?)', $vendorIds);
	        }
		}
        return $this;
    }
    
	public function addVendorFilter($vendorIds)
    {
        if(!empty($vendorIds)){
    		return $this->addFieldToFilter('e.vendor_id', array('in' => $vendorIds));
        }
        return $this;
    }
    
	public function load($printQuery = false, $logQuery = false)
    {
    	if ($this->isLoaded()) {
            return $this;
        }
        $this->_initSelect();
        if ($this->_applyFilters) {
            $this->_applyDateRangeFilter();
            $this->_applyStoresFilter();
            $this->_applyOrderStatusFilter();
            $this->_applyVendorFilter();
        }
        $grandParent = $this->getGrandParent();
        //echo $this->getSelect().'<br/>';
        return call_user_func(array($grandParent, 'load'), $printQuery, $logQuery);
    }
    
	protected function getGrandParent(){
    	return get_parent_class(get_parent_class($this));
    }
}
