<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Report bestsellers collection
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class SM_Vendors_Model_Override_Sales_Resource_Report_Bestsellers_Collection
    extends Mage_Sales_Model_Resource_Report_Bestsellers_Collection
{
    /**
     * Rating limit
     *
     * @var int
     */
    protected $_ratingLimit        = 5;

    /**
     * Columns for select
     *
     * @var array
     */
    protected $_selectedColumns    = array();

    /**
     * Initialize custom resource model
     *
     */
    public function __construct()
    {
        parent::_construct();
        $this->setModel('adminhtml/report_item');
        $this->_resource = Mage::getResourceModel('sales/report')->init('sales/bestsellers_aggregated_daily');
        $this->setConnection($this->getResource()->getReadConnection());
        // overwrite default behaviour
        $this->_applyFilters = false;
    }

    /**
     * Retrieve columns for select
     *
     * @return array
     */
    protected function _getSelectedColumns()
    {
        $adapter = $this->getConnection();

        if (!$this->_selectedColumns) {
            if ($this->isTotals()) {
                $this->_selectedColumns = $this->getAggregatedColumns();
            } else {
                $this->_selectedColumns = array(
                    'period'         =>  sprintf('MAX(%s)', $adapter->getDateFormatSql('period', '%Y-%m-%d')),
                    'qty_ordered'    => 'SUM(qty_ordered)',
                    'product_id'     => 'product_id',
                    'product_name'   => 'MAX(product_name)',
                    'product_price'  => 'MAX(product_price)',
                	'vendor_id'		=> 'product_vendor_id.value',
                );
                if ('year' == $this->_period) {
                    $this->_selectedColumns['period'] = $adapter->getDateFormatSql('period', '%Y');
                } elseif ('month' == $this->_period) {
                    $this->_selectedColumns['period'] = $adapter->getDateFormatSql('period', '%Y-%m');
                }
            }
        }
        return $this->_selectedColumns;
    }

    /**
     * Make select object for date boundary
     *
     * @param mixed $from
     * @param mixed $to
     * @return Zend_Db_Select
     */
    protected function _makeBoundarySelect($from, $to)
    {
        $adapter = $this->getConnection();
        $cols    = $this->_getSelectedColumns();
        $cols['qty_ordered'] = 'SUM(qty_ordered)';
        $sel     = $adapter->select()
            ->from($this->getResource()->getMainTable(), $cols)
            ->where('period >= ?', $from)
            ->where('period <= ?', $to)
            ->group('product_id')
            ->order('qty_ordered')
            ->limit($this->_ratingLimit);

        $this->_applyStoresFilterToSelect($sel);

        return $sel;
    }

    /**
     * Add selected data
     *
     * @return Mage_Sales_Model_Resource_Report_Bestsellers_Collection
     */
    protected function _initSelect()
    {
        $select = $this->getSelect();

        // if grouping by product, not by period
        if (!$this->_period) {
            $cols = $this->_getSelectedColumns();
            $cols['qty_ordered'] = 'SUM(qty_ordered)';
            if ($this->_from || $this->_to) {
                $mainTable = $this->getTable('sales/bestsellers_aggregated_daily');
                $select->from($mainTable, $cols);
            } else {
                $mainTable = $this->getTable('sales/bestsellers_aggregated_yearly');
                $select->from($mainTable, $cols);
            }

            //exclude removed products
            $subSelect = $this->getConnection()->select();
            $subSelect->from(array('existed_products' => $this->getTable('catalog/product')), new Zend_Db_Expr('1)'));

            $select->exists($subSelect, $mainTable . '.product_id = existed_products.entity_id')
                ->group('product_id')
                ->order('qty_ordered ' . Varien_Db_Select::SQL_DESC)
                ->limit($this->_ratingLimit);

            return $this;
        }

        if ('year' == $this->_period) {
            $mainTable = $this->getTable('sales/bestsellers_aggregated_yearly');
            $select->from($mainTable, $this->_getSelectedColumns());
        } elseif ('month' == $this->_period) {
            $mainTable = $this->getTable('sales/bestsellers_aggregated_monthly');
            $select->from($mainTable, $this->_getSelectedColumns());
        } else {
            $mainTable = $this->getTable('sales/bestsellers_aggregated_daily');
            $select->from($mainTable, $this->_getSelectedColumns());
        }
        if (!$this->isTotals()) {
            $select->group(array('period', 'product_id'));
        }
        $select->where('rating_pos <= ?', $this->_ratingLimit);

        return $this;
    }

    /**
     * Get SQL for get record count
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();
        $select = clone $this->getSelect();
        $select->reset(Zend_Db_Select::ORDER);
        return $this->getConnection()->select()->from($select, 'COUNT(*)');
    }

    /**
     * Set ids for store restrictions
     *
     * @param  array $storeIds
     * @return Mage_Sales_Model_Resource_Report_Bestsellers_Collection
     */
    public function addStoreRestrictions($storeIds)
    {
        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }
        $currentStoreIds = $this->_storesIds;
        if (isset($currentStoreIds) && $currentStoreIds != Mage_Core_Model_App::ADMIN_STORE_ID
            && $currentStoreIds != array(Mage_Core_Model_App::ADMIN_STORE_ID)) {
            if (!is_array($currentStoreIds)) {
                $currentStoreIds = array($currentStoreIds);
            }
            $this->_storesIds = array_intersect($currentStoreIds, $storeIds);
        } else {
            $this->_storesIds = $storeIds;
        }

        return $this;
    }

    /**
     * Redeclare parent method for applying filters after parent method
     * but before adding unions and calculating totals
     *
     * @return Mage_Sales_Model_Resource_Report_Bestsellers_Collection
     */
    protected function _beforeLoad()
    {
        parent::_beforeLoad();

        $this->_applyStoresFilter();
        $this->_applyDateRangeFilter();

        if ($this->_period) {
            //
            $selectUnions = array();

            // apply date boundaries (before calling $this->_applyDateRangeFilter())
            $dtFormat   = Varien_Date::DATE_INTERNAL_FORMAT;
            $periodFrom = (!is_null($this->_from) ? new Zend_Date($this->_from, $dtFormat) : null);
            $periodTo   = (!is_null($this->_to)   ? new Zend_Date($this->_to,   $dtFormat) : null);
            if ('year' == $this->_period) {

                if ($periodFrom) {
                    // not the first day of the year
                    if ($periodFrom->toValue(Zend_Date::MONTH) != 1 || $periodFrom->toValue(Zend_Date::DAY) != 1) {
                        $dtFrom = $periodFrom->getDate();
                        // last day of the year
                        $dtTo = $periodFrom->getDate()->setMonth(12)->setDay(31);
                        if (!$periodTo || $dtTo->isEarlier($periodTo)) {
                            $selectUnions[] = $this->_makeBoundarySelect(
                                $dtFrom->toString($dtFormat),
                                $dtTo->toString($dtFormat)
                            );

                            // first day of the next year
                            $this->_from = $periodFrom->getDate()
                                ->addYear(1)
                                ->setMonth(1)
                                ->setDay(1)
                                ->toString($dtFormat);
                        }
                    }
                }

                if ($periodTo) {
                    // not the last day of the year
                    if ($periodTo->toValue(Zend_Date::MONTH) != 12 || $periodTo->toValue(Zend_Date::DAY) != 31) {
                        $dtFrom = $periodTo->getDate()->setMonth(1)->setDay(1);  // first day of the year
                        $dtTo = $periodTo->getDate();
                        if (!$periodFrom || $dtFrom->isLater($periodFrom)) {
                            $selectUnions[] = $this->_makeBoundarySelect(
                                $dtFrom->toString($dtFormat),
                                $dtTo->toString($dtFormat)
                            );

                            // last day of the previous year
                            $this->_to = $periodTo->getDate()
                                ->subYear(1)
                                ->setMonth(12)
                                ->setDay(31)
                                ->toString($dtFormat);
                        }
                    }
                }

                if ($periodFrom && $periodTo) {
                    // the same year
                    if ($periodFrom->toValue(Zend_Date::YEAR) == $periodTo->toValue(Zend_Date::YEAR)) {
                        $dtFrom = $periodFrom->getDate();
                        $dtTo = $periodTo->getDate();
                        $selectUnions[] = $this->_makeBoundarySelect(
                            $dtFrom->toString($dtFormat),
                            $dtTo->toString($dtFormat)
                        );

                        $this->getSelect()->where('1<>1');
                    }
                }

            }
            else if ('month' == $this->_period) {
                if ($periodFrom) {
                    // not the first day of the month
                    if ($periodFrom->toValue(Zend_Date::DAY) != 1) {
                        $dtFrom = $periodFrom->getDate();
                        // last day of the month
                        $dtTo = $periodFrom->getDate()->addMonth(1)->setDay(1)->subDay(1);
                        if (!$periodTo || $dtTo->isEarlier($periodTo)) {
                            $selectUnions[] = $this->_makeBoundarySelect(
                                $dtFrom->toString($dtFormat),
                                $dtTo->toString($dtFormat)
                            );

                            // first day of the next month
                            $this->_from = $periodFrom->getDate()->addMonth(1)->setDay(1)->toString($dtFormat);
                        }
                    }
                }

                if ($periodTo) {
                    // not the last day of the month
                    if ($periodTo->toValue(Zend_Date::DAY) != $periodTo->toValue(Zend_Date::MONTH_DAYS)) {
                        $dtFrom = $periodTo->getDate()->setDay(1);  // first day of the month
                        $dtTo = $periodTo->getDate();
                        if (!$periodFrom || $dtFrom->isLater($periodFrom)) {
                            $selectUnions[] = $this->_makeBoundarySelect(
                                $dtFrom->toString($dtFormat),
                                $dtTo->toString($dtFormat)
                            );

                            // last day of the previous month
                            $this->_to = $periodTo->getDate()->setDay(1)->subDay(1)->toString($dtFormat);
                        }
                    }
                }

                if ($periodFrom && $periodTo) {
                    // the same month
                    if ($periodFrom->toValue(Zend_Date::YEAR) == $periodTo->toValue(Zend_Date::YEAR)
                        && $periodFrom->toValue(Zend_Date::MONTH) == $periodTo->toValue(Zend_Date::MONTH)
                    ) {
                        $dtFrom = $periodFrom->getDate();
                        $dtTo = $periodTo->getDate();
                        $selectUnions[] = $this->_makeBoundarySelect(
                            $dtFrom->toString($dtFormat),
                            $dtTo->toString($dtFormat)
                        );

                        $this->getSelect()->where('1<>1');
                    }
                }

            }

            // add unions to select
            if ($selectUnions) {
                $unionParts = array();
                $cloneSelect = clone $this->getSelect();
                $helper = Mage::getResourceHelper('core');
                $unionParts[] = '(' . $cloneSelect . ')';
                foreach ($selectUnions as $union) {
                    $query = $helper->getQueryUsingAnalyticFunction($union);
                    $unionParts[] = '(' . $query . ')';
                }
                $this->getSelect()->reset()->union($unionParts, Zend_Db_Select::SQL_UNION_ALL);
            }

            if ($this->isTotals()) {
                // calculate total
                $cloneSelect = clone $this->getSelect();
                $this->getSelect()->reset()->from($cloneSelect, $this->getAggregatedColumns());
            } else {
                // add sorting
                $this->getSelect()->order(array('period ASC', 'qty_ordered DESC'));
            }
        }
		$adapter = Mage::getSingleton('core/resource')->getConnection('core_write');
        $product  = Mage::getResourceSingleton('catalog/product');
        $attr     = $product->getAttribute('sm_product_vendor_id');
            $joinExprProductVendorId       = array(
                'product_vendor_id.entity_id = sales_bestsellers_aggregated_yearly.product_id',
                'product_vendor_id.store_id = sales_bestsellers_aggregated_yearly.store_id',
                $adapter->quoteInto('product_vendor_id.entity_type_id = ?', $product->getTypeId()),
                $adapter->quoteInto('product_vendor_id.attribute_id = ?', $attr->getAttributeId())
            );
            $joinExprProductVendorId        = implode(' AND ', $joinExprProductVendorId);
            $joinExprProductDefaultVendorId = array(
                'product_default_vendor_id.entity_id = sales_bestsellers_aggregated_yearly.product_id',
                'product_default_vendor_id.store_id = 0',
                $adapter->quoteInto('product_default_vendor_id.entity_type_id = ?', $product->getTypeId()),
                $adapter->quoteInto('product_default_vendor_id.attribute_id = ?', $attr->getAttributeId())
            );
            $joinExprProductDefaultVendorId = implode(' AND ', $joinExprProductDefaultVendorId);
            $this->getSelect()->joinLeft(
                array(
                    'product_vendor_id' => $attr->getBackend()->getTable()),
                $joinExprProductVendorId,
                array()
            )
            ->joinLeft(
                array(
                    'product_default_vendor_id' => $attr->getBackend()->getTable()),
                $joinExprProductDefaultVendorId,
                array()
            );
        if($vendor = Mage::helper('smvendors')->getVendorLogin()){
        	$this->getSelect()->where('product_vendor_id.value = ?', $vendor->getId());
        }
		//die($this->getSelect());
        return $this;
    }
    
	protected function _applyStoresFilterToSelect(Zend_Db_Select $select)
    {
        $nullCheck = false;
        $storeIds  = $this->_storesIds;

        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }

        $storeIds = array_unique($storeIds);

        if ($index = array_search(null, $storeIds)) {
            unset($storeIds[$index]);
            $nullCheck = true;
        }

        $storeIds[0] = ($storeIds[0] == '') ? 0 : $storeIds[0];

        if ($nullCheck) {
            $select->where('sales_bestsellers_aggregated_yearly.store_id IN(?) OR sales_bestsellers_aggregated_yearly.store_id IS NULL', $storeIds);
        } else {
            $select->where('sales_bestsellers_aggregated_yearly.store_id IN(?)', $storeIds);
        }

        return $this;
    }
}
