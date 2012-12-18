<?php
/**
 * Author : Nguyen Trung Hieu
 * Email : hieunt@smartosc.com
 * Date: 8/17/12
 * Time: 10:19 AM
 */

class SM_Vendors_Block_Override_Adminhtml_Report_Customer_Totals_Grid extends Mage_Adminhtml_Block_Report_Customer_Totals_Grid {

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sm/vendors/reports/customer/grid.phtml');
    }

    /**
     *  Get Vendor Id From filter and save in registry
     */
    protected function _prepareCollection()
    {
        $filter = $this->getParam($this->getVarNameFilter(), null);

        if (is_string($filter)) {
            $data = array();
            $filter = base64_decode($filter);
            parse_str(urldecode($filter), $data);
            if (isset($data['vendors']) && $data['vendors'] != '0') {
                if (Mage::registry('vendor_id')) Mage::unregister('vendor_id');
                Mage::register('vendor_id',$data['vendors']);
            }
        }
        parent::_prepareCollection();
    }

    /**
     * @return SM_Vendors_Block_Override_Adminhtml_Report_Customer_Totals_Grid|void
     */
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

    /**
     * @return array
     * get vendor list to display on filter
     */
    public function getVendors() {
        $vendorArr = array();
        $vendors = Mage::getModel('smvendors/vendor')->getCollection();
        $vendorArr[] =  array('value' => 0 , 'label' => 'All Vendor');
        foreach($vendors as $vendor) {
            $vendorArr[] = array('value' => $vendor->getVendor_id(),'label' => $vendor->getVendor_name());
        }

        return $vendorArr;

    }
}