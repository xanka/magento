<?php

class SM_Vendors_Model_Mysql4_Reports_Product_Viewed_Collection extends SM_Vendors_Model_Override_Reports_Resource_Product_Viewed_Collection
{
    protected $_isLoaded = false;
    public function addVendorFilter($vendorIds=array()){
        $select = $this->getSelect();

        $resource = Mage::getSingleton('core/resource');
        $select->joinLeft(array('cpv'=>$resource->getTableName('catalog_product_entity_varchar')),'e.entity_id = cpv.entity_id AND cpv.attribute_id=962',array('vendor_id'=> 'cpv.value'))
            ->joinLeft(array('v'=> $resource->getTableName('sm_vendor')),'cpv.value = v.vendor_id',array('vendor_name'=>'v.vendor_name'))
        ;
        if(!empty($vendorIds)){
            $select->where('cpv.value=?',array($vendorIds));
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

            parent::load($printQuery, $logQuery);

            return $this;

        }
        catch(Exception $e){
            //echo $e->getMessage();
            //die($this->getSelect());
        }
        $this->_isLoaded = true;
    }

}
