<?php
/**
 * Author : Nguyen Trung Hieu
 * Email : hieunt@smartosc.com
 * Date: 9/5/12
 * Time: 6:08 PM
 */

class SM_Vendors_Model_Override_Tax_Resource_Report_Tax_Createdat extends Mage_Tax_Model_Resource_Report_Tax_Createdat {

    protected function _aggregateByOrder($aggregationField, $from, $to)
    {
        // convert input dates to UTC to be comparable with DATETIME fields in DB

        $from = $this->_dateToUtc($from);
        $to = $this->_dateToUtc($to);

        $orderTable = $this->getTable('sales/order');
        $sourceTable =  $this->getTable('tax/sales_order_tax');

        $this->_checkDates($from, $to);
        $writeAdapter = $this->_getWriteAdapter();
        $writeAdapter->beginTransaction();

        $vendorOrderTable = $this->getTable('smvendors/order');
        $sel = clone $writeAdapter->select();
        $sel
            ->from(array('ven' => $vendorOrderTable),array(
            'vendor_id'));
        $sel->join(array('vendor' => $this->getTable('smvendors/vendor')),'`vendor`.`vendor_id` = `ven`.`vendor_id`',array('vendor' => 'vendor_name'));

        $sel->joinLeft($orderTable,'`ven`.`order_id` = `sales_flat_order`.`entity_id`');
        $orderTable = new Zend_Db_Expr(sprintf('(%s)', $sel));

        try {
            if ($from !== null || $to !== null) {
                $subSelect = $this->_getTableDateRangeSelect(
                    $orderTable,
                    'created_at', 'updated_at', $from, $to
                );
            } else {
                $subSelect = null;
            }

            $this->_clearTableByDateRange($this->getMainTable(), $from, $to, $subSelect);
            // convert dates from UTC to current admin timezone
            $periodExpr = $writeAdapter->getDatePartSql(
                $this->getStoreTZOffsetQuery(
                    array('e' => $this->getTable('sales/order')),
                    'e.' . $aggregationField,
                    $from, $to
                )
            );

            $columns = array(
                'period'                => $periodExpr,
                'store_id'              => 'e.store_id',
                'code'                  => 'tax.code',
                'order_status'          => 'e.status',
                'percent'               => 'MAX(tax.' . $writeAdapter->quoteIdentifier('percent') .')',
                'orders_count'          => 'COUNT(DISTINCT e.entity_id)',
                'tax_base_amount_sum'   => 'SUM(tax.base_amount * e.base_to_global_rate)',
                'vendor'                => 'e.vendor',
                'vendor_id'             => 'e.vendor_id'
            );

            $select = $writeAdapter->select();
            $select->from(array('tax' => $sourceTable), $columns)
                ->joinInner(array('e' => $orderTable), 'e.entity_id = tax.order_id', array())
                ->useStraightJoin();

            $select->where('e.state NOT IN (?)', array(
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                Mage_Sales_Model_Order::STATE_NEW
            ));

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(array($periodExpr, 'e.store_id', 'code', 'tax.percent', 'e.status'
            ,'e.vendor_id'
            ));

            $insertQuery = $writeAdapter->insertFromSelect($select, $this->getMainTable(), array_keys($columns));
            $writeAdapter->query($insertQuery);

            $select->reset();

            $columns = array(
                'period'                => 'period',
                'store_id'              => new Zend_Db_Expr(Mage_Core_Model_App::ADMIN_STORE_ID),
                'code'                  => 'code',
                'order_status'          => 'order_status',
                'percent'               => 'MAX(' . $writeAdapter->quoteIdentifier('percent') . ')',
                'orders_count'          => 'SUM(orders_count)',
                'tax_base_amount_sum'   => 'SUM(tax_base_amount_sum)',
                'vendor'                => 'vendor',
                'vendor_id'             => 'vendor_id'
            );

            $select
                ->from($this->getMainTable(), $columns)
                ->where('store_id <> ?', 0);

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(array('period', 'code', 'percent', 'order_status'
            ,'vendor_id'
            ));

            $insertQuery = $writeAdapter->insertFromSelect($select, $this->getMainTable(), array_keys($columns));
            $writeAdapter->query($insertQuery);
            $writeAdapter->commit();
        } catch (Exception $e) {
            $writeAdapter->rollBack();
            throw $e;
        }

        return $this;
    }
}