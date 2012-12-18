<?php
/**
 * Author : Nguyen Trung Hieu
 * Email : hieunt@smartosc.com
 * Date: 9/5/12
 * Time: 4:35 PM
 */

class SM_Vendors_Model_Override_Sales_Resource_Report_Invoiced_Collection_Invoiced extends Mage_Sales_Model_Resource_Report_Invoiced_Collection_Invoiced {
    var $vendorFilter = false;
    var $vendorId;

    public function __construct() {
        parent::__construct();
        if (Mage::registry('vendor_id')) {
            $this->vendorFilter(Mage::registry('vendor_id'));
        }
        else $this->vendorFilter();
    }

    protected function _getSelectedColumns()
    {
        $adapter = $this->getConnection();
        if ('month' == $this->_period) {
            $this->_periodFormat = $adapter->getDateFormatSql('period', '%Y-%m');
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = $adapter->getDateExtractSql('period', Varien_Db_Adapter_Interface::INTERVAL_YEAR);
        } else {
            $this->_periodFormat = $adapter->getDateFormatSql('period', '%Y-%m-%d');
        }

        if (!$this->isTotals()) {
            $this->_selectedColumns = array(
                'period'                => $this->_periodFormat,
                'orders_count'          => 'SUM(orders_count)',
                'orders_invoiced'       => 'SUM(orders_invoiced)',
                'invoiced'              => 'SUM(invoiced)',
                'invoiced_captured'     => 'SUM(invoiced_captured)',
                'invoiced_not_captured' => 'SUM(invoiced_not_captured)',
                'vendor'                => 'vendor'
            );
        }

        if ($this->isTotals()) {
            $this->_selectedColumns = $this->getAggregatedColumns();
        }

        return $this->_selectedColumns;
    }

    protected function _initSelect()
    {

        if ($this->vendorId > 0) {
            $this->getSelect()
                ->from($this->getResource()->getMainTable() , $this->_getSelectedColumns())
                ->where('`vendor_id` = '.$this->vendorId);
            if (!$this->isTotals()) {
                $this->getSelect()->group(array($this->_periodFormat,'vendor_id'));
                $this->getSelect()->having('SUM(orders_count) > 0');
            }
            return $this;
        }
        else {
            $this->getSelect()->from($this->getResource()->getMainTable() , $this->_getSelectedColumns());
            if (!$this->isTotals()) {
                $this->getSelect()->group(array($this->_periodFormat,'vendor_id'));
                $this->getSelect()->having('SUM(orders_count) > 0');
            }
            return $this;
        }

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


}