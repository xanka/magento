<?php
/**
 * Author : Nguyen Trung Hieu
 * Email : hieunt@smartosc.com
 * Date: 9/6/12
 * Time: 10:24 AM
 */

class SM_Vendors_Model_Override_Tax_Resource_Report_Tax_Updatedat extends SM_Vendors_Model_Override_Tax_Resource_Report_Tax_Createdat {
    protected function _construct()
    {
        $this->_init('tax/tax_order_aggregated_updated', 'id');
    }

    /**
     * Aggregate Tax data by order updated at
     *
     * @param mixed $from
     * @param mixed $to
     * @return Mage_Tax_Model_Resource_Report_Tax_Updatedat
     */
    public function aggregate($from = null, $to = null)
    {
        return $this->_aggregateByOrder('updated_at', $from, $to);
    }
}