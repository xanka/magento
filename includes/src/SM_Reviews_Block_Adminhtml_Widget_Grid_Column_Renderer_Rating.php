<?php
class SM_Reviews_Block_Adminhtml_Widget_Grid_Column_Renderer_Rating extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function __construct() {

    }

    public function render(Varien_Object $row) {
        return $this->_getValue($row);
    }

    protected function _getValue(Varien_Object $row) {
        $val = intval($row->getData($this->getColumn()->getIndex()));
        return Mage::helper('smreviews')->renderRating($val);
    }
}