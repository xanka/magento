<?php
/**
 * Author : Nguyen Trung Hieu
 * Email : hieunt@smartosc.com
 * Date: 8/17/12
 * Time: 10:22 AM
 */

class SM_Vendors_Model_Override_Reports_Resource_Totals_Collection extends Mage_Reports_Model_Resource_Customer_Totals_Collection {
    var $vendorFilter = false;
    var $vendorId;
    protected function _construct() {
        if (Mage::registry('vendor_id')) {
            $this->vendorFilter(Mage::registry('vendor_id'));
        }
        else $this->vendorFilter();

        $this->_init('smvendors/order');
        $this
            ->addFilterToMap('entity_id', 'main_table.entity_id')
            ->addFilterToMap('customer_id', 'order.customer_id')
            ->addFilterToMap('quote_address_id', 'order.quote_address_id');


        $this->_useAnalyticFunction = true;
    }

    protected function _joinFields($from = '', $to = '')
    {
        $this->getSelect()->joinLeft(array('order' => $this->getTable('sales/order')),'`order`.`entity_id` = `main_table`.`order_id`',array());



        $this->joinCustomerName()
            ->groupByCustomer()
            ->addOrdersCount()
            ->addAttributeToFilter('`order`.`created_at`', array('from' => $from, 'to' => $to, 'datetime' => true));
        $this->getSelect()
            ->join(array('ven' => $this->getTable('smvendors/vendor')),'`ven`.`vendor_id` = `main_table`.`vendor_id`' ,array('vendor' => 'vendor_name'));
        if ($this->vendorFilter && $this->vendorId > 0  )  {
            $this->getSelect()->where('`main_table`.`vendor_id` = '.$this->vendorId);
        }
        else {
            $this->getSelect()->group('vendor');
        }

        return $this;

    }

    public function vendorFilter($vendor = 0) {
        if ($this->vendorFilter) return;
        if ($vendor != 0) {
            $this->vendorId = $vendor;
            $this->vendorFilter = true;
            return;
        }
        $user = Mage::getSingleton('admin/session')->getUser();
        if (!$user) {
            return;
        } else {
            $_vendor = Mage::getModel('smvendors/vendor')->loadByUserId($user->getUserId());
            if ($_vendor->getId()) {
                $this->vendorFilter = true;
                $this->vendorId = $_vendor->getId();
                return;
            }
            return;
        }

    }

    public function setStoreIds($storeIds)
    {
        if ($storeIds) {
            $this->addAttributeToFilter('`order`.`store_id`', array('in' => (array)$storeIds));
            $this->addSumAvgTotals(1)
                ->orderByTotalAmount();
        } else {
            $this->addSumAvgTotals()
                ->orderByTotalAmount();
        }

        return $this;
    }

    public function joinCustomerName($alias = 'name') {
        $fields      = array('order.customer_firstname', 'order.customer_lastname');
        $fieldConcat = $this->getConnection()->getConcatSql($fields, ' ');
        $this->getSelect()->columns(array($alias => $fieldConcat));
        return $this;

    }

    public function groupByCustomer(){
        $this->getSelect()
            ->where('order.customer_id IS NOT NULL')
            ->group('order.customer_id');

        /*
         * Allow Analytic functions usage
         */
        $this->_useAnalyticFunction = true;

        return $this;

    }
}