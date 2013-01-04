<?php

class SM_Vendors_Model_Mysql4_Reports_Product_Sold_Collection extends Mage_Reports_Model_Mysql4_Product_Sold_Collection
{
    protected $_isLoaded = false;
    public function addVendorFilter($vendorIds=array()){
        $select = $this->getSelect();
        $resource = Mage::getSingleton('core/resource');
        $select->joinLeft(array('v'=> $resource->getTableName('sm_vendor')),'v.vendor_id = order_items.vendor_id',array('vendor_name'=>'v.vendor_name'))
        ;
        if(!empty($vendorIds)){
            $select->where('order_items.vendor_id=?',array($vendorIds));
        }
        return $this;
    }

    public function load($printQuery = false, $logQuery = false)
    {


        try{
            //if(!$this->_isLoaded){
            $filter = Mage::app()->getRequest()->getParam('filter');
            $filter = base64_decode($filter);
            $output = array();
            parse_str($filter, $output);
            if(!empty($output['report_vendor_id'])){
                $this->addVendorFilter(array($output['report_vendor_id']));
            }
            else{
                $this->addVendorFilter();
            }
            //}
            parent::load($printQuery, $logQuery);
            //echo $this->getSelect().'<br/><br/>';
            return $this;
        }
        catch(Exception $e){
            //echo $e->getMessage();
            //die($this->getSelect());
        }
        $this->_isLoaded = true;
    }
}
