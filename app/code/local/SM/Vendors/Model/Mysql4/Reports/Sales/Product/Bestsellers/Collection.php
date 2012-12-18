<?php

class SM_Vendors_Model_Mysql4_Reports_Sales_Product_Bestsellers_Collection
    extends Mage_Sales_Model_Resource_Report_Bestsellers_Collection
{
    protected $_vendorIds = array();
    /**
     * Add selected data
     *
     * @return Mage_Sales_Model_Resource_Report_Bestsellers_Collection
     */
    protected function _initSelect()
    {
        $select = $this->getSelect();

        // if grouping by product, not by period
        if (!$this->_period) {
            $cols = $this->_getSelectedColumns();
            $cols['qty_ordered'] = 'SUM(qty_ordered)';
            if ($this->_from || $this->_to) {
                $mainTable = $this->getTable('sales/bestsellers_aggregated_daily');
                $select->from(array('main_table'=>$mainTable), $cols);
            } else {
                $mainTable = $this->getTable('sales/bestsellers_aggregated_yearly');
                $select->from(array('main_table'=>$mainTable), $cols);
            }

            //exclude removed products
            $subSelect = $this->getConnection()->select();
            $subSelect->from(array('existed_products' => $this->getTable('catalog/product')), new Zend_Db_Expr('1)'));

            $select->exists($subSelect, 'main_table.product_id = existed_products.entity_id')
                ->group('product_id')
                ->order('qty_ordered ' . Varien_Db_Select::SQL_DESC)
                ->limit($this->_ratingLimit);

            return $this;
        }

        if ('year' == $this->_period) {
            $mainTable = $this->getTable('sales/bestsellers_aggregated_yearly');
            $select->from(array('main_table'=>$mainTable), $this->_getSelectedColumns());
        } elseif ('month' == $this->_period) {
            $mainTable = $this->getTable('sales/bestsellers_aggregated_monthly');
            $select->from(array('main_table'=>$mainTable), $this->_getSelectedColumns());
        } else {
            $mainTable = $this->getTable('sales/bestsellers_aggregated_daily');
            $select->from(array('main_table'=>$mainTable), $this->_getSelectedColumns());
        }
        if (!$this->isTotals()) {
            $select->group(array('period', 'product_id'));
        }
        $select->where('main_table.rating_pos <= ?', $this->_ratingLimit);

        if($vendor= Mage::helper('smvendors')->getVendorLogin()){
            //$this->addVendorToFilter($vendor->getId());
        }


        return $this;
    }

    /**
     * Make select object for date boundary
     *
     * @param mixed $from
     * @param mixed $to
     * @return Zend_Db_Select
     */
    protected function _makeBoundarySelect($from, $to)
    {
        $adapter = $this->getConnection();
        $cols    = $this->_getSelectedColumns();
        $cols['qty_ordered'] = 'SUM(qty_ordered)';
        $sel     = $adapter->select()
            ->from(array('main_table'=>$this->getResource()->getMainTable()), $cols)
            ->where('period >= ?', $from)
            ->where('period <= ?', $to)
            ->group('product_id')
            ->order('qty_ordered')
            ->limit($this->_ratingLimit);

        if(!empty($this->_vendorIds)){
            $sel->group('main_table.vendor_id')->where('main_table.vendor_id=?',$this->_vendorIds);
        }

        $this->_applyStoresFilterToSelect($sel);

        return $sel;
    }
    // hieunt : hardcode attribute id of vendor
    public function addVendorFilter($vendorIds=array()){
        $this->_vendorIds = $vendorIds;
        $select = $this->getSelect();

          $resource = Mage::getSingleton('core/resource');
          $select->joinLeft(array('cp'=> $this->getTable('catalog/product')),'main_table.product_id=cp.entity_id',array())
                  ->joinLeft(array('cpv'=>$resource->getTableName('catalog_product_entity_varchar')),'cp.entity_id = cpv.entity_id AND cpv.attribute_id=962',array('vendor_id'=> 'cpv.value'))
                  ->joinLeft(array('v'=> $resource->getTableName('sm_vendor')),'cpv.value = v.vendor_id',array('vendor_name'=>'v.vendor_name'))
          ;
          if(!empty($vendorIds)){
              $select->where('cpv.value=?',array($vendorIds));
          }


//        if(!empty($vendorIds)){
//            $select->group('main_table.vendor_id')->where('main_table.vendor_id=?',array($vendorIds));
//        }

        return $this;
    }

    protected function _applyStoresFilterToSelect(Zend_Db_Select $select)
    {
        $nullCheck = false;
        $storeIds  = $this->_storesIds;

        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }

        $storeIds = array_unique($storeIds);

        if ($index = array_search(null, $storeIds)) {
            unset($storeIds[$index]);
            $nullCheck = true;
        }

        $storeIds[0] = ($storeIds[0] == '') ? 0 : $storeIds[0];

        if ($nullCheck) {
            $select->where('main_table.store_id IN(?) OR main_table.store_id IS NULL', $storeIds);
        } else {
            $select->where('main_table.store_id IN(?)', $storeIds);
        }

        return $this;
    }

    public function load($printQuery = false, $logQuery = false)
    {
        try{
            parent::load($printQuery, $logQuery);
            // echo $this->getSelect().'<br/><br/>';
            return $this;
        }
        catch(Exception $e){
            //echo $e->getMessage();
            die($this->getSelect());
        }

    }

}
