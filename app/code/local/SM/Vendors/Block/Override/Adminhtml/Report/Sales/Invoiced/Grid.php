<?php
/**
 * Author : Nguyen Trung Hieu
 * Email : hieunt@smartosc.com
 * Date: 9/5/12
 * Time: 4:40 PM
 */

class SM_Vendors_Block_Override_Adminhtml_Report_Sales_Invoiced_Grid extends Mage_Adminhtml_Block_Report_Sales_Invoiced_Grid {

    protected function _getVendorIds() {
        $filterData = $this->getFilterData();

        if ($filterData) {
            $vendorIdsData = $filterData->getData('vendors');
            if (!empty($vendorIdsData)) {
                $vendorIds = array($vendorIdsData);
            } else {
                $vendorIds = array();
            }
        } else {
            $vendorIds = array();
        }

        $vendorIds = array_values($vendorIds);
        return $vendorIds;
    }
    protected function _prepareCollection() {
        $vendor_id = $this->_getVendorIds();
        if (sizeof($vendor_id) > 0) {
            $vendor_id =  $vendor_id[0];
            if (Mage::registry('vendor_id')) Mage::unregister('vendor_id');
            Mage::register('vendor_id',$vendor_id);
        }
        parent::_prepareCollection();
    }
    protected function _prepareColumns() {

        parent::_prepareColumns();
        if ($vendor = Mage::helper('smvendors')->getVendorLogin()) {
            return $this;
        }

        $this->addColumn('vendor', array(
            'header'    => Mage::helper('reports')->__('Vendor Name'),
            'index'     => 'vendor'
        ));

    }
}