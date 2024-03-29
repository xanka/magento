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
 * @package     Mage_Reports
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Reports orders collection
 *
 * @category    Mage
 * @package     Mage_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class SM_Vendors_Model_Resource_Reports_Order_Collection extends SM_Vendors_Model_Mysql4_Order_Collection
{
    /**
     * Is live
     *
     * @var boolean
     */
    protected $_isLive   = false;

    public function __construct(){
    	parent::__construct();
    	$this->joinParentOrder();
    }
    
    /**
     * Check range for live mode
     *
     * @param unknown_type $range
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function checkIsLive($range)
    {
        $this->_isLive = (bool)!Mage::getStoreConfig('sales/dashboard/use_aggregated_data');
        return $this;
    }

    /**
     * Retrieve is live flag for rep
     *
     * @return boolean
     */
    public function isLive()
    {
        return $this->_isLive;
    }

    /**
     * Prepare report summary
     *
     * @param string $range
     * @param mixed $customStart
     * @param mixed $customEnd
     * @param int $isFilter
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function prepareSummary($range, $customStart, $customEnd, $isFilter = 0)
    {
        $this->checkIsLive($range);
        if ($this->_isLive) {
            $this->_prepareSummaryLive($range, $customStart, $customEnd, $isFilter);
        } else {
            $this->_prepareSummaryAggregated($range, $customStart, $customEnd, $isFilter);
        }

        return $this;
    }

    /**
     * Prepare report summary from live data
     *
     * @param string $range
     * @param mixed $customStart
     * @param mixed $customEnd
     * @param int $isFilter
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    protected function _prepareSummaryLive($range, $customStart, $customEnd, $isFilter = 0)
    {
        $this->setMainTable('smvendors/order');
        $adapter = $this->getConnection();

        /**
         * Reset all columns, because result will group only by 'created_at' field
         */
        $this->getSelect()->reset(Zend_Db_Select::COLUMNS);

        $expression = sprintf('%s - %s - %s - (%s - %s - %s)',
            $adapter->getIfNullSql('main_table.base_total_invoiced', 0),
            $adapter->getIfNullSql('main_table.base_tax_invoiced', 0),
            $adapter->getIfNullSql('main_table.base_shipping_invoiced', 0),
            $adapter->getIfNullSql('main_table.base_total_refunded', 0),
            $adapter->getIfNullSql('main_table.base_tax_refunded', 0),
            $adapter->getIfNullSql('main_table.base_shipping_refunded', 0)
        );
        if ($isFilter == 0) {
            $this->getSelect()->columns(array(
                'revenue' => new Zend_Db_Expr(
                    sprintf('SUM((%s) * %s)', $expression,
                        $adapter->getIfNullSql('po.base_to_global_rate', 0)
                    )
                 )
            ));
        } else {
            $this->getSelect()->columns(array(
                'revenue' => new Zend_Db_Expr(sprintf('SUM(%s)', $expression))
            ));
        }

        $dateRange = $this->getDateRange($range, $customStart, $customEnd);

        $tzRangeOffsetExpression = $this->_getTZRangeOffsetExpression(
            $range, 'po.created_at', $dateRange['from'], $dateRange['to']
        );

        $this->getSelect()
            ->columns(array(
                'quantity' => 'COUNT(main_table.entity_id)',
                'range' => $tzRangeOffsetExpression,
            ))
            ->where('main_table.status NOT IN (?)', array(
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
           		 Mage_Sales_Model_Order::STATE_NEW)
            )
            ->order('range', Zend_Db_Select::SQL_ASC)
            ->group($tzRangeOffsetExpression);

        $this->addFieldToFilter('po.created_at', $dateRange);
		//$this->joinParentOrder();
        return $this;
    }

    /**
     * Prepare report summary from aggregated data
     *
     * @param string $range
     * @param mixed $customStart
     * @param mixed $customEnd
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    protected function _prepareSummaryAggregated($range, $customStart, $customEnd)
    {
        $this->setMainTable('sales/order_aggregated_created');
        /**
         * Reset all columns, because result will group only by 'created_at' field
         */
        $this->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $rangePeriod = $this->_getRangeExpressionForAttribute($range, 'main_table.period');

        $tableName = $this->getConnection()->quoteIdentifier('main_table.period');
        $rangePeriod2 = str_replace($tableName, "MIN($tableName)", $rangePeriod);

        $this->getSelect()->columns(array(
            'revenue'  => 'SUM(main_table.total_revenue_amount)',
            'quantity' => 'SUM(main_table.orders_count)',
            'range' => $rangePeriod2,
        ))
        ->order('range')
        ->group($rangePeriod);

        $this->getSelect()->where(
            $this->_getConditionSql('main_table.period', $this->getDateRange($range, $customStart, $customEnd))
        );

        $statuses = Mage::getSingleton('sales/config')
            ->getOrderStatusesForState(Mage_Sales_Model_Order::STATE_CANCELED);

        if (empty($statuses)) {
            $statuses = array(0);
        }
        $this->addFieldToFilter('main_table.status', array('nin' => $statuses));

        return $this;
    }

    /**
     * Get range expression
     *
     * @param string $range
     * @return Zend_Db_Expr
     */
    protected function _getRangeExpression($range)
    {
        switch ($range)
        {
            case '24h':
                $expression = $this->getConnection()->getConcatSql(array(
                    $this->getConnection()->getDateFormatSql('{{attribute}}', '%Y-%m-%d %H:'),
                    $this->getConnection()->quote('00')
                ));
                break;
            case '7d':
            case '1m':
                $expression = $this->getConnection()->getDateFormatSql('{{attribute}}', '%Y-%m-%d');
                break;
            case '1y':
            case '2y':
            case 'custom':
            default:
                $expression = $this->getConnection()->getDateFormatSql('{{attribute}}', '%Y-%m');
                break;
        }

        return $expression;
    }

    /**
     * Retrieve range expression adapted for attribute
     *
     * @param string $range
     * @param string $attribute
     * @return string
     */
    protected function _getRangeExpressionForAttribute($range, $attribute)
    {
        $expression = $this->_getRangeExpression($range);
        return str_replace('{{attribute}}', $this->getConnection()->quoteIdentifier($attribute), $expression);
    }

    /**
     * Retrieve query for attribute with timezone conversion
     *
     * @param string $range
     * @param string $attribute
     * @param mixed $from
     * @param mixed $to
     * @return string
     */
    protected function _getTZRangeOffsetExpression($range, $attribute, $from = null, $to = null)
    {
        return str_replace(
            '{{attribute}}',
            Mage::getResourceModel('sales/report_order')
                    ->getStoreTZOffsetQuery($this->getMainTable(), $attribute, $from, $to),
            $this->_getRangeExpression($range)
        );
    }

    /**
     * Retrieve range expression with timezone conversion adapted for attribute
     *
     * @param string $range
     * @param string $attribute
     * @param string $tzFrom
     * @param string $tzTo
     * @return string
     */
    protected function _getTZRangeExpressionForAttribute($range, $attribute, $tzFrom = '+00:00', $tzTo = null)
    {
        if (null == $tzTo) {
            $tzTo = Mage::app()->getLocale()->storeDate()->toString(Zend_Date::GMT_DIFF_SEP);
        }
        $adapter = $this->getConnection();
        $expression = $this->_getRangeExpression($range);
        $attribute  = $adapter->quoteIdentifier($attribute);
        $periodExpr = $adapter->getDateAddSql($attribute, $tzTo, Varien_Db_Adapter_Interface::INTERVAL_HOUR);

        return str_replace('{{attribute}}', $periodExpr, $expression);
    }

    /**
     * Calculate From and To dates (or times) by given period
     *
     * @param string $range
     * @param string $customStart
     * @param string $customEnd
     * @param boolean $returnObjects
     * @return array
     */
    public function getDateRange($range, $customStart, $customEnd, $returnObjects = false)
    {
        $dateEnd   = Mage::app()->getLocale()->date();
        $dateStart = clone $dateEnd;

        // go to the end of a day
        $dateEnd->setHour(23);
        $dateEnd->setMinute(59);
        $dateEnd->setSecond(59);

        $dateStart->setHour(0);
        $dateStart->setMinute(0);
        $dateStart->setSecond(0);

        switch ($range)
        {
            case '24h':
                $dateEnd = Mage::app()->getLocale()->date();
                $dateEnd->addHour(1);
                $dateStart = clone $dateEnd;
                $dateStart->subDay(1);
                break;

            case '7d':
                // substract 6 days we need to include
                // only today and not hte last one from range
                $dateStart->subDay(6);
                break;

            case '1m':
                $dateStart->setDay(Mage::getStoreConfig('reports/dashboard/mtd_start'));
                break;

            case 'custom':
                $dateStart = $customStart ? $customStart : $dateEnd;
                $dateEnd   = $customEnd ? $customEnd : $dateEnd;
                break;

            case '1y':
            case '2y':
                $startMonthDay = explode(',', Mage::getStoreConfig('reports/dashboard/ytd_start'));
                $startMonth = isset($startMonthDay[0]) ? (int)$startMonthDay[0] : 1;
                $startDay = isset($startMonthDay[1]) ? (int)$startMonthDay[1] : 1;
                $dateStart->setMonth($startMonth);
                $dateStart->setDay($startDay);
                if ($range == '2y') {
                    $dateStart->subYear(1);
                }
                break;
        }

        $dateStart->setTimezone('Etc/UTC');
        $dateEnd->setTimezone('Etc/UTC');

        if ($returnObjects) {
            return array($dateStart, $dateEnd);
        } else {
            return array('from' => $dateStart, 'to' => $dateEnd, 'datetime' => true);
        }
    }

    /**
     * Add item count expression
     *
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function addItemCountExpr()
    {
    	$tblOrderItem  = Mage::getSingleton('core/resource')->getTableName('sales/order_item');
        $this->getSelect()->join(array('oi'=>$tblOrderItem),'oi.order_id = po.entity_id AND oi.vendor_id = main_table.vendor_id',array('items_count'=>'COUNT(oi.item_id)'));
    	//$this->getSelect()->columns(array('items_count' => 'total_item_count'), 'main_table');
        $this->getSelect()->group('main_table.entity_id');
        
        return $this;
    }

    /**
     * Calculate totals report
     *
     * @param int $isFilter
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function calculateTotals($isFilter = 0)
    {
        if ($this->isLive()) {
            $this->_calculateTotalsLive($isFilter);
        } else {
            $this->_calculateTotalsAggregated($isFilter);
        }

        return $this;
    }

    /**
     * Calculate totals live report
     *
     * @param int $isFilter
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    protected function _calculateTotalsLive($isFilter = 0)
    {
        $this->setMainTable('smvendors/order');
        $this->removeAllFieldsFromSelect();

        $adapter = $this->getConnection();

        $baseTotalInvoiced    = $adapter->getIfNullSql('main_table.base_total_invoiced', 0);
        $baseTotalRefunded    = $adapter->getIfNullSql('main_table.base_total_refunded', 0);
        $baseTaxInvoiced      = $adapter->getIfNullSql('main_table.base_tax_invoiced', 0);
        $baseTaxRefunded      = $adapter->getIfNullSql('main_table.base_tax_refunded', 0);
        $baseShippingInvoiced = $adapter->getIfNullSql('main_table.base_shipping_invoiced', 0);
        $baseShippingRefunded = $adapter->getIfNullSql('main_table.base_shipping_refunded', 0);

        $revenueExp = sprintf('%s - %s - %s - (%s - %s - %s)',
            $baseTotalInvoiced,
            $baseTaxInvoiced,
            $baseShippingInvoiced,
            $baseTotalRefunded,
            $baseTaxRefunded,
            $baseShippingRefunded
        );
        $taxExp = sprintf('%s - %s', $baseTaxInvoiced, $baseTaxRefunded);
        $shippingExp = sprintf('%s - %s', $baseShippingInvoiced, $baseShippingRefunded);

        if ($isFilter == 0) {
            $rateExp = $adapter->getIfNullSql('po.base_to_global_rate', 0);
            $this->getSelect()->columns(
                array(
                    'revenue'  => new Zend_Db_Expr(sprintf('SUM((%s) * %s)', $revenueExp, $rateExp)),
                    'tax'      => new Zend_Db_Expr(sprintf('SUM((%s) * %s)', $taxExp, $rateExp)),
                    'shipping' => new Zend_Db_Expr(sprintf('SUM((%s) * %s)', $shippingExp, $rateExp))
                )
            );
        } else {
            $this->getSelect()->columns(
                array(
                    'revenue'  => new Zend_Db_Expr(sprintf('SUM(%s)', $revenueExp)),
                    'tax'      => new Zend_Db_Expr(sprintf('SUM(%s)', $taxExp)),
                    'shipping' => new Zend_Db_Expr(sprintf('SUM(%s)', $shippingExp))
                )
            );
        }

        $this->getSelect()->columns(array(
            'quantity' => 'COUNT(main_table.entity_id)'
        ))
        ->where('main_table.status NOT IN (?)', array(
            Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
            Mage_Sales_Model_Order::STATE_NEW)
         );
		//$this->joinParentOrder();
        return $this;
    }

    /**
     * Calculate totals aggregated report
     *
     * @param int $isFilter
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    protected function _calculateTotalsAggregated($isFilter = 0)
    {
        $this->setMainTable('sales/order_aggregated_created');
        $this->removeAllFieldsFromSelect();

        $this->getSelect()->columns(array(
            'revenue'  => 'SUM(main_table.total_revenue_amount)',
            'tax'      => 'SUM(main_table.total_tax_amount_actual)',
            'shipping' => 'SUM(main_table.total_shipping_amount_actual)',
            'quantity' => 'SUM(orders_count)',
        ));

        $statuses = Mage::getSingleton('sales/config')
            ->getOrderStatusesForState(Mage_Sales_Model_Order::STATE_CANCELED);

        if (empty($statuses)) {
            $statuses = array(0);
        }

        $this->getSelect()->where('main_table.status NOT IN(?)', $statuses);
		//$this->joinParentOrder();
        return $this;
    }

    /**
     * Calculate lifitime sales
     *
     * @param int $isFilter
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function calculateSales($isFilter = 0)
    {
        $statuses = Mage::getSingleton('sales/config')
            ->getOrderStatusesForState(Mage_Sales_Model_Order::STATE_CANCELED);

        if (empty($statuses)) {
            $statuses = array(0);
        }
        $adapter = $this->getConnection();

        if (Mage::getStoreConfig('sales/dashboard/use_aggregated_data')) {
            $this->setMainTable('sales/order_aggregated_created');
            $this->removeAllFieldsFromSelect();
            $averageExpr = $adapter->getCheckSql(
                'SUM(main_table.orders_count) > 0',
                'SUM(main_table.total_revenue_amount)/SUM(main_table.orders_count)',
                0);
            $this->getSelect()->columns(array(
                'lifetime' => 'SUM(main_table.total_revenue_amount)',
                'average'  => $averageExpr
            ));

            if (!$isFilter) {
                $this->addFieldToFilter('store_id',
                    array('eq' => Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId())
                );
            }
            $this->getSelect()->where('main_table.status NOT IN(?)', $statuses);
        } else {
            $this->setMainTable('smvendors/order');
            $this->removeAllFieldsFromSelect();

            $expr = sprintf('%s - %s - %s - (%s - %s - %s)',
                $adapter->getIfNullSql('main_table.base_total_invoiced', 0),
                $adapter->getIfNullSql('main_table.base_tax_invoiced', 0),
                $adapter->getIfNullSql('main_table.base_shipping_invoiced', 0),
                $adapter->getIfNullSql('main_table.base_total_refunded', 0),
                $adapter->getIfNullSql('main_table.base_tax_refunded', 0),
                $adapter->getIfNullSql('main_table.base_shipping_refunded', 0)
            );

            if ($isFilter == 0) {
                $expr = '(' . $expr . ') * po.base_to_global_rate';
            }

            $this->getSelect()
                ->columns(array(
                    'lifetime' => "SUM({$expr})",
                    'average'  => "AVG({$expr})"
                ))
                ->where('main_table.status NOT IN(?)', $statuses)
                ->where('po.state NOT IN(?)', array(
                    Mage_Sales_Model_Order::STATE_NEW,
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)
                );
        }
        return $this;
    }

    /**
     * Set date range
     *
     * @param string $from
     * @param string $to
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function setDateRange($from, $to)
    {
        $this->_reset()
            ->addFieldToFilter('po.created_at', array('from' => $from, 'to' => $to))
            ->addFieldToFilter('po.state', array('neq' => Mage_Sales_Model_Order::STATE_CANCELED))
            ->getSelect()
                ->columns(array('orders' => 'COUNT(DISTINCT(main_table.entity_id))'))
                ->group('main_table.entity_id');

        $this->getSelect()->columns(array(
            'items' => 'SUM(main_table.total_qty_ordered)')
        );

        return $this;
    }

    /**
     * Set store filter collection
     *
     * @param array $storeIds
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function setStoreIds($storeIds)
    {
        $adapter = $this->getConnection();
        $baseSubtotalInvoiced = $adapter->getIfNullSql('main_table.base_subtotal_invoiced', 0);
        $baseDiscountRefunded = $adapter->getIfNullSql('main_table.base_discount_refunded', 0);
        $baseSubtotalRefunded = $adapter->getIfNullSql('main_table.base_subtotal_refunded', 0);
        $baseDiscountInvoiced = $adapter->getIfNullSql('main_table.base_discount_invoiced', 0);
        $baseTotalInvocedCost = $adapter->getIfNullSql('main_table.base_total_invoiced_cost', 0);
        if ($storeIds) {
            $this->getSelect()->columns(array(
                'subtotal'  => 'SUM(main_table.base_subtotal)',
                'tax'       => 'SUM(main_table.base_tax_amount)',
                'shipping'  => 'SUM(main_table.base_shipping_amount)',
                'discount'  => 'SUM(main_table.base_discount_amount)',
                'total'     => 'SUM(main_table.base_grand_total)',
                'invoiced'  => 'SUM(main_table.base_total_paid)',
                'refunded'  => 'SUM(main_table.base_total_refunded)',
                'profit'    => "SUM($baseSubtotalInvoiced) "
                                . "+ SUM({$baseDiscountRefunded}) - SUM({$baseSubtotalRefunded}) "
                                . "- SUM({$baseDiscountInvoiced}) - SUM({$baseTotalInvocedCost})"
            ));
        } else {
            $this->getSelect()->columns(array(
                'subtotal'  => 'SUM(main_table.base_subtotal * po.base_to_global_rate)',
                'tax'       => 'SUM(main_table.base_tax_amount * po.base_to_global_rate)',
                'shipping'  => 'SUM(main_table.base_shipping_amount * po.base_to_global_rate)',
                'discount'  => 'SUM(main_table.base_discount_amount * po.base_to_global_rate)',
                'total'     => 'SUM(main_table.base_grand_total * po.base_to_global_rate)',
                'invoiced'  => 'SUM(main_table.base_total_paid * po.base_to_global_rate)',
                'refunded'  => 'SUM(main_table.base_total_refunded * po.base_to_global_rate)',
                'profit'    => "SUM({$baseSubtotalInvoiced} *  po.base_to_global_rate) "
                                . "+ SUM({$baseDiscountRefunded} * po.base_to_global_rate) "
                                . "- SUM({$baseSubtotalRefunded} * po.base_to_global_rate) "
                                . "- SUM({$baseDiscountInvoiced} * po.base_to_global_rate) "
                                . "- SUM({$baseTotalInvocedCost} * po.base_to_global_rate)"
            ));
        }

        return $this;
    }

    /**
     * Add group By customer attribute
     *
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function groupByCustomer()
    {
        $this->getSelect()
            ->where('po.customer_id IS NOT NULL')
            ->group('po.customer_id');

        /*
         * Allow Analytic functions usage
         */
        $this->_useAnalyticFunction = true;

        return $this;
    }

    /**
     * Join Customer Name (concat)
     *
     * @param string $alias
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function joinCustomerName($alias = 'name')
    {
        $fields      = array('po.customer_firstname', 'po.customer_lastname');
        $fieldConcat = $this->getConnection()->getConcatSql($fields, ' ');
        $this->getSelect()->columns(array($alias => $fieldConcat));
        return $this;
    }

    /**
     * Add Order count field to select
     *
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function addOrdersCount()
    {
        $this->addFieldToFilter('state', array('neq' => Mage_Sales_Model_Order::STATE_CANCELED));
        $this->getSelect()
            ->columns(array('orders_count' => 'COUNT(main_table.entity_id)'));

        return $this;
    }

    /**
     * Add revenue
     *
     * @param boolean $convertCurrency
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function addRevenueToSelect($convertCurrency = false)
    {
        if ($convertCurrency) {
            $this->getSelect()->columns(array(
                'revenue' => '(main_table.base_grand_total * po.base_to_global_rate)'
            ));
        } else {
            $this->getSelect()->columns(array(
                'revenue' => 'base_grand_total'
            ));
        }

        return $this;
    }

    /**
     * Add summary average totals
     *
     * @param int $storeId
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function addSumAvgTotals($storeId = 0)
    {
        $adapter = $this->getConnection();
        $baseSubtotalRefunded = $adapter->getIfNullSql('main_table.base_subtotal_refunded', 0);
        $baseSubtotalCanceled = $adapter->getIfNullSql('main_table.base_subtotal_canceled', 0);
        $baseDiscountCanceled = $adapter->getIfNullSql('main_table.base_discount_canceled', 0);

        /**
         * calculate average and total amount
         */
        $expr = ($storeId == 0)
            ? "(main_table.base_subtotal -
            {$baseSubtotalRefunded} - {$baseSubtotalCanceled} - ABS(main_table.base_discount_amount) -
            {$baseDiscountCanceled}) * po.base_to_global_rate"
            : "main_table.base_subtotal - {$baseSubtotalCanceled} - {$baseSubtotalRefunded} -
            ABS(main_table.base_discount_amount) - {$baseDiscountCanceled}";

        $this->getSelect()
            ->columns(array('orders_avg_amount' => "AVG({$expr})"))
            ->columns(array('orders_sum_amount' => "SUM({$expr})"));

        return $this;
    }

    /**
     * Sort order by total amount
     *
     * @param string $dir
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function orderByTotalAmount($dir = self::SORT_ORDER_DESC)
    {
        $this->getSelect()->order('orders_sum_amount ' . $dir);
        return $this;
    }

    /**
     * Order by orders count
     *
     * @param unknown_type $dir
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function orderByOrdersCount($dir = self::SORT_ORDER_DESC)
    {
        $this->getSelect()->order('orders_count ' . $dir);
        return $this;
    }

    /**
     * Order by customer registration
     *
     * @param unknown_type $dir
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function orderByCustomerRegistration($dir = self::SORT_ORDER_DESC)
    {
        $this->setOrder('customer_id', $dir);
        return $this;
    }

    /**
     * Sort order by order created_at date
     *
     * @param string $dir
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function orderByCreatedAt($dir = self::SORT_ORDER_DESC)
    {
        $this->getSelect()->order('po.created_at ' .$dir);
        return $this;
    }

    /**
     * Get select count sql
     *
     * @return unknown
     */
    public function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->reset(Zend_Db_Select::GROUP);
        $countSelect->reset(Zend_Db_Select::HAVING);
        $countSelect->columns("COUNT(DISTINCT main_table.entity_id)");

        return $countSelect;
    }

    /**
     * Initialize initial fields to select
     *
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    protected function _initInitialFieldsToSelect()
    {
        // No fields should be initialized
        return $this;
    }

    /**
     * Add period filter by created_at attribute
     *
     * @param string $period
     * @return Mage_Reports_Model_Resource_Order_Collection
     */
    public function addCreateAtPeriodFilter($period)
    {
        list($from, $to) = $this->getDateRange($period, 0, 0, true);

        $this->checkIsLive($period);

        if ($this->isLive()) {
            $fieldToFilter = 'created_at';
        } else {
            $fieldToFilter = 'period';
        }

        $this->addFieldToFilter($fieldToFilter, array(
            'from'  => $from->toString(Varien_Date::DATETIME_INTERNAL_FORMAT),
            'to'    => $to->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)
        ));

        return $this;
    }
    
    public function joinParentOrder(){
    	$mainTableOrder = Mage::getSingleton('core/resource')->getTableName('sales/order');
    	$this->getSelect()
           ->join(array('po' => $mainTableOrder),'main_table.order_id = po.entity_id',null);
        
        if($vendor = Mage::helper('smvendors')->getVendorLogin()){
        	$this->addFieldToFilter('main_table.vendor_id',$vendor->getId());
        }
        
        return $this;
    }
    
    public function setPageSize($size){
    	$this->getSelect()->limit($size);
    	return $this;
    }
}
