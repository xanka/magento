<?php
/**
 * Author : Nguyen Trung Hieu
 * Email : hieunt@smartosc.com
 * Date: 9/5/12
 * Time: 3:19 PM
 */

class SM_Vendors_Model_Override_Sales_Resource_Report_Invoiced extends Mage_Sales_Model_Resource_Report_Invoiced {
    protected function _aggregateByInvoiceCreatedAt($from, $to) {
        $table       = $this->getTable('sales/invoiced_aggregated');
        $sourceTable = $this->getTable('sales/invoice');
        $orderTable  = $this->getTable('sales/order');
        $helper      = Mage::getResourceHelper('core');
        $adapter     = $this->_getWriteAdapter();

        $adapter->beginTransaction();

        $sel = clone $adapter->select();
        $vendorOrderTable = $this->getTable('smvendors/order');

        $sel->reset()
            ->from(array('ven' => $vendorOrderTable),array('ven_total_invoiced' =>'total_invoiced',
            'ven_base_total_invoiced' => 'base_total_invoiced',
            'ven_base_total_paid' => 'base_total_paid',
            'vendor_id'));
        $sel->join(array('vendor' => $this->getTable('smvendors/vendor')),'`vendor`.`vendor_id` = `ven`.`vendor_id`',array('vendor' => 'vendor_name'));

        $sel->joinLeft($orderTable,'`ven`.`order_id` = `sales_flat_order`.`entity_id`');
        $orderTable = new Zend_Db_Expr(sprintf('(%s)', $sel));

        try {
            if ($from !== null || $to !== null) {
                $subSelect = $this->_getTableDateRangeRelatedSelect(
                    $sourceTable, $orderTable, array('order_id'=>'entity_id'),
                    'created_at', 'updated_at', $from, $to
                );
            } else {
                $subSelect = null;
            }

            $this->_clearTableByDateRange($table, $from, $to, $subSelect);
            // convert dates from UTC to current admin timezone
            $periodExpr = $adapter->getDatePartSql(
                $this->getStoreTZOffsetQuery(
                    array('source_table' => $sourceTable),
                    'source_table.created_at', $from, $to
                )
            );
            $columns = array(
                // convert dates from UTC to current admin timezone
                'period'                => $periodExpr,
                'store_id'              => 'order_table.store_id',
                'order_status'          => 'order_table.status',
                'orders_count'          => new Zend_Db_Expr('COUNT(order_table.entity_id)'),
                'orders_invoiced'       => new Zend_Db_Expr('COUNT(order_table.entity_id)'),
                'invoiced'              => new Zend_Db_Expr('SUM(order_table.ven_base_total_invoiced'
                    . ' * order_table.base_to_global_rate)'),
                'invoiced_captured'     => new Zend_Db_Expr('SUM(order_table.ven_base_total_paid'
                    . ' * order_table.base_to_global_rate)'),
                'invoiced_not_captured' => new Zend_Db_Expr(
                    'SUM((order_table.ven_base_total_invoiced - order_table.ven_base_total_paid)'
                        . ' * order_table.base_to_global_rate)'),
                'vendor_id'             => 'order_table.vendor_id',
                'vendor'                => 'order_table.vendor'
            );

            $select = $adapter->select();
            $select->from(array('source_table' => $sourceTable), $columns)
                ->joinInner(
                array('order_table' => $orderTable),
                $adapter->quoteInto(
                    'source_table.order_id = order_table.entity_id AND order_table.state <> ?',
                    Mage_Sales_Model_Order::STATE_CANCELED),
                array()
            );

            $filterSubSelect = $adapter->select();
            $filterSubSelect->from(array('filter_source_table' => $sourceTable), 'MAX(filter_source_table.entity_id)')
                ->where('filter_source_table.order_id = source_table.order_id');

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->where('source_table.entity_id = (?)', new Zend_Db_Expr($filterSubSelect));
            unset($filterSubSelect);

            $select->group(array(
                $periodExpr,
                'order_table.store_id',
                'order_table.status',
                'order_table.vendor_id'
            ));

            $select->having('orders_count > 0');
            $insertQuery = $helper->getInsertFromSelectUsingAnalytic($select, $table, array_keys($columns));
            $adapter->query($insertQuery);
            $select->reset();

            $columns = array(
                'period'                => 'period',
                'store_id'              => new Zend_Db_Expr(Mage_Core_Model_App::ADMIN_STORE_ID),
                'order_status'          => 'order_status',
                'orders_count'          => new Zend_Db_Expr('SUM(orders_count)'),
                'orders_invoiced'       => new Zend_Db_Expr('SUM(orders_invoiced)'),
                'invoiced'              => new Zend_Db_Expr('SUM(invoiced)'),
                'invoiced_captured'     => new Zend_Db_Expr('SUM(invoiced_captured)'),
                'invoiced_not_captured' => new Zend_Db_Expr('SUM(invoiced_not_captured)'),
                'vendor_id'             => 'vendor_id',
                'vendor'                => 'vendor'
            );

            $select
                ->from($table, $columns)
                ->where('store_id <> ?', 0);

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(array(
                'period',
                'order_status',
                'vendor_id'
            ));

            $insertQuery = $helper->getInsertFromSelectUsingAnalytic($select, $table, array_keys($columns));
            $adapter->query($insertQuery);
            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollBack();
            throw $e;
        }

        return $this;
    }

    protected function _aggregateByOrderCreatedAt($from, $to) {
        $table       = $this->getTable('sales/invoiced_aggregated_order');
        $sourceTable = $this->getTable('sales/order');
        $adapter     = $this->_getWriteAdapter();

        $sel = clone $adapter->select();
        $vendorOrderTable = $this->getTable('smvendors/order');

        $sel->reset()
            ->from(array('ven' => $vendorOrderTable),array('ven_total_invoiced' =>'total_invoiced',
            'ven_base_total_invoiced' => 'base_total_invoiced',
            'ven_base_total_paid' => 'base_total_paid',
            'vendor_id'));
        $sel->join(array('vendor' => $this->getTable('smvendors/vendor')),'`vendor`.`vendor_id` = `ven`.`vendor_id`',array('vendor' => 'vendor_name'));

        $sel->joinLeft($sourceTable,'`ven`.`order_id` = `sales_flat_order`.`entity_id`');
        $sourceTable = new Zend_Db_Expr(sprintf('(%s)', $sel));

        if ($from !== null || $to !== null) {
            $subSelect = $this->_getTableDateRangeSelect($sourceTable, 'created_at', 'updated_at', $from, $to);
        } else {
            $subSelect = null;
        }

        $this->_clearTableByDateRange($table, $from, $to, $subSelect);
        // convert dates from UTC to current admin timezone
        $periodExpr = $adapter->getDatePartSql(
            $this->getStoreTZOffsetQuery(
                $sourceTable, 'created_at', $from, $to
            )
        );

        $columns = array(
            'period'                => $periodExpr,
            'store_id'              => 'store_id',
            'order_status'          => 'status',
            'orders_count'          => new Zend_Db_Expr('COUNT(ven_base_total_invoiced)'),
            'orders_invoiced'       => new Zend_Db_Expr(
                sprintf('SUM(%s)',
                    $adapter->getCheckSql('ven_base_total_invoiced > 0', 1, 0)
                )
            ),
            'invoiced'              => new Zend_Db_Expr(
                sprintf('SUM(%s * %s)',
                    $adapter->getIfNullSql('ven_base_total_invoiced',0),
                    $adapter->getIfNullSql('base_to_global_rate',0)
                )
            ),
            'invoiced_captured'     => new Zend_Db_Expr(
                sprintf('SUM(%s * %s)',
                    $adapter->getIfNullSql('ven_base_total_paid',0),
                    $adapter->getIfNullSql('base_to_global_rate',0)
                )
            ),
            'invoiced_not_captured' => new Zend_Db_Expr(
                sprintf('SUM((%s - %s) * %s)',
                    $adapter->getIfNullSql('ven_base_total_invoiced',0),
                    $adapter->getIfNullSql('ven_base_total_paid',0),
                    $adapter->getIfNullSql('base_to_global_rate',0)
                )
            ),
            'vendor_id'             => 'vendor_id',
            'vendor'                => 'vendor'

        );

        $select = $adapter->select();
        $select->from($sourceTable, $columns)
            ->where('state <> ?', Mage_Sales_Model_Order::STATE_CANCELED);

        if ($subSelect !== null) {
            $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
        }

        $select->group(array(
            $periodExpr,
            'store_id',
            'status',
            'vendor_id'
        ));

        $select->having('orders_count > 0');

        $helper      = Mage::getResourceHelper('core');
        $insertQuery = $helper->getInsertFromSelectUsingAnalytic($select, $table, array_keys($columns));
        $adapter->query($insertQuery);
        $select->reset();

        $columns = array(
            'period'                => 'period',
            'store_id'              => new Zend_Db_Expr(Mage_Core_Model_App::ADMIN_STORE_ID),
            'order_status'          => 'order_status',
            'orders_count'          => new Zend_Db_Expr('SUM(orders_count)'),
            'orders_invoiced'       => new Zend_Db_Expr('SUM(orders_invoiced)'),
            'invoiced'              => new Zend_Db_Expr('SUM(invoiced)'),
            'invoiced_captured'     => new Zend_Db_Expr('SUM(invoiced_captured)'),
            'invoiced_not_captured' => new Zend_Db_Expr('SUM(invoiced_not_captured)'),
            'vendor_id'             => 'vendor_id',
            'vendor'                => 'vendor'
        );

        $select->from($table, $columns)
            ->where('store_id <> ?', 0);

        if ($subSelect !== null) {
            $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
        }

        $select->group(array(
            'period',
            'order_status',
            'vendor_id'
        ));

        $helper      = Mage::getResourceHelper('core');
        $insertQuery = $helper->getInsertFromSelectUsingAnalytic($select, $table, array_keys($columns));
        $adapter->query($insertQuery);


        return $this;
    }
}