<?php

class SM_Vendors_Model_Mysql4_Sales_Report_Shipping_Collection_Order
    extends Mage_Sales_Model_Mysql4_Report_Shipping_Collection_Order
{
    protected $_vendorIds = array();

    protected function _initSelect()
    {
        $this->getSelect()->from(
            $this->getResource()->getMainTable() ,
            $this->_getSelectedColumns()
        );

        $this->getSelect()->joinLeft(array('v'=>$this->getTable('smvendors/vendor')),'v.vendor_id = '.$this->getResource()->getMainTable().'.vendor_id',array('vendor_name'));
        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->getSelect()->group(array(
                $this->_periodFormat,
                'shipping_description',
                $this->getResource()->getMainTable().'.vendor_id'
            ));
        }
        if ($this->isSubTotals()) {
            $this->getSelect()->group(array(
                $this->_periodFormat,
                $this->getResource()->getMainTable().'.vendor_id'
            ));
        }

        if($vendor = Mage::helper('smvendors')->getVendorLogin()){
            $this->getSelect()->where('v.vendor_id=?',$vendor->getId());
        }
        else {

            if (Mage::registry('current_vendor_report')) {

                $current_vendor_report = Mage::registry('current_vendor_report');
                if (count($current_vendor_report) ==     0) {
                    //show all
                }

                else {
                    foreach($current_vendor_report as $_vendor) {
                        $this->getSelect()->where('v.vendor_id=?',$_vendor);
                        break;
                    }
                }
            }
        }

//        echo $this->getSelect();
//        die;
        return $this;
    }

    public function addVendorFilter($vendorIds=array()){
//        $this->_vendorIds = $vendorIds;
//        $select = $this->getSelect();
//        echo $this->getSelect();
//        die;
//        $resource = Mage::getSingleton('core/resource');
//        $select->joinLeft(array('cp'=> $this->getTable('catalog/product')),'main_table.product_id=cp.entity_id',array())
//            ->joinLeft(array('cpv'=>$resource->getTableName('catalog_product_entity_varchar')),'cp.entity_id = cpv.entity_id AND cpv.attribute_id=962',array('vendor_id'=> 'cpv.value'))
//            ->joinLeft(array('vc'=> $resource->getTableName('sm_vendor')),'cpv.value = vc.vendor_id',array('vendor_name'=>'vc.vendor_name'))
//        ;
//        if(!empty($vendorIds)){
//            $select->where('cpv.value=?',array($vendorIds));
//        }
//
//
////        if(!empty($vendorIds)){
////            $select->group('main_table.vendor_id')->where('main_table.vendor_id=?',array($vendorIds));
////        }
//
//        return $this;
    }
}
