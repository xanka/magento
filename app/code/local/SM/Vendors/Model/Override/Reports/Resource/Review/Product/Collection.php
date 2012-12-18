<?php
/**
 * Author : Nguyen Trung Hieu
 * Email : hieunt@smartosc.com
 * Date: 8/15/12
 * Time: 2:49 PM
 */


class SM_Vendors_Model_Override_Reports_Resource_Review_Product_Collection extends Mage_Reports_Model_Resource_Review_Product_Collection {

    var $vendorFilter = false;
    var $vendorId;
    protected function _construct()
    {
        parent::_construct();
        $this->_useAnalyticFunction = true;
        if (Mage::registry('vendor_id')) {
            $this->vendorFilter(Mage::registry('vendor_id'));
        }
        else $this->vendorFilter();
    }
    public function joinReview() {
        parent::joinReview();
//        var_dump( $this->vendorId );
//        die();

        $attributeId = $attributeId = Mage::getResourceModel('eav/entity_attribute')
										->getIdByCode('catalog_product','sm_product_vendor_id');
        
        $subSelect21 = clone $this->getSelect();
        $subSelect22 = clone $this->getSelect();

        $subSelect22->reset()
            ->from(array('ven' => $this->getTable('smvendors/vendor')), array(0 => 'vendor_name'))
            ->where('`ven`.`vendor_id` = `var`.`value`');

        if ($this->vendorFilter && $this->vendorId > 0) {

            $subSelect21->reset()
                ->from(array('var' => $this->getTable('catalog_product_entity_varchar')), array(0 => 'entity_id' , 1 => 'value' , 'vendor' => new Zend_Db_Expr(sprintf('(%s)', $subSelect22))))
                ->where('attribute_id = ?',$attributeId)
                ->where('`var`.`value` = '.$this->vendorId);
        }
         else $subSelect21->reset()
            ->from(array('var' => $this->getTable('catalog_product_entity_varchar')), array(0 => 'entity_id' , 1 => 'value' , 'vendor' => new Zend_Db_Expr(sprintf('(%s)', $subSelect22))))
            ->where('attribute_id = ?',$attributeId);

        $this->getSelect()
            ->from(array('sub' => new Zend_Db_Expr(sprintf('(%s)', $subSelect21))),array( 0 => 'vendor'))
            ->where('`e`.`entity_id` = `sub`.`entity_id`');

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