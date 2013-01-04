<?php
/**
 * Author : Nguyen Trung Hieu
 * Email : hieunt@smartosc.com
 * Date: 9/5/12
 * Time: 5:39 PM
 */

class SM_Vendors_Model_Override_Tax_Resource_Report_Collection extends Mage_Tax_Model_Mysql4_Report_Collection {

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
        if ('month' == $this->_period) {
            $this->_periodFormat = $this->getConnection()->getDateFormatSql('period', '%Y-%m');
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = $this->getConnection()->getDateFormatSql('period', '%Y');
        } else {
            $this->_periodFormat = $this->getConnection()->getDateFormatSql('period', '%Y-%m-%d');
        }

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->_selectedColumns = array(
                'period'                => $this->_periodFormat,
                'code'                  => 'code',
                'percent'               => 'percent',
                'orders_count'          => 'SUM(orders_count)',
                'tax_base_amount_sum'   => 'SUM(tax_base_amount_sum)',
                'vendor_id'             => 'vendor_id',
                'vendor'                => 'vendor'
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

    protected function _initSelect()
    {
        if ($this->vendorId > 0) {
            $this
                ->getSelect()
                ->from($this->getResource()->getMainTable() , $this->_getSelectedColumns())
                ->where('`vendor_id` = '.$this->vendorId);;
            if (!$this->isTotals() && !$this->isSubTotals()) {
                $this->getSelect()->group(array($this->_periodFormat, 'code', 'percent','vendor_id'));
            }

            if ($this->isSubTotals()) {
                $this->getSelect()->group(array(
                    $this->_periodFormat
                ));
            }
        }
        else {
            $this->getSelect()->from($this->getResource()->getMainTable() , $this->_getSelectedColumns());
            if (!$this->isTotals() && !$this->isSubTotals()) {
                $this->getSelect()->group(array($this->_periodFormat, 'code', 'percent','vendor_id'));
            }

            if ($this->isSubTotals()) {
                $this->getSelect()->group(array(
                    $this->_periodFormat
                ));
            }
        }


        /**
         * Allow to use analytic function
         */
        $this->_useAnalyticFunction = true;

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


}