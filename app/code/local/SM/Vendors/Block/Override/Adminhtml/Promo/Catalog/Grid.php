<?php
class SM_Vendors_Block_Override_Adminhtml_Promo_Catalog_Grid extends Mage_Adminhtml_Block_Promo_Catalog_Grid
{

    protected function _prepareCollection()
    {
        $grandParent = get_parent_class(get_parent_class($this));
        /* @var $grandParent Mage_Adminhtml_Block_Widget_Grid */
        $collection = Mage::getModel('catalogrule/rule')
            ->getResourceCollection();
        /* @var $collection Mage_SalesRule_Model_Mysql4_Rule_Collection */
        if($vendor = Mage::helper('smvendors')->getVendorLogin()) {
            $collection->addFilter('vendor_id', $vendor->getId());
        } else {
            $tableName = $collection->getTable('smvendors/vendor');
            $collection->getSelect()
                ->joinLeft($tableName, "main_table.vendor_id = {$tableName}.vendor_id")
                ->columns("{$tableName}.vendor_name");
        }
        
        $this->setCollection($collection);
        return call_user_func(array($grandParent, '_prepareCollection'));
    }

    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        
        if(!Mage::helper('smvendors')->getVendorLogin()) {
            $this->addColumnAfter('vendor_name', array(
                'header'    => Mage::helper('salesrule')->__('Vendor'),
                'align'     =>'left',
                'width'     => '120px',
                'index'     => 'vendor_name',
            ), 'name');

            $this->sortColumnsByOrder();
        }
        return $this;
    }
}
