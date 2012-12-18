<?php
/**
 * Date: 10/27/12
 * Time: 3:05 AM
 */

class SM_Planet_Block_Adminhtml_Partner extends Mage_Adminhtml_Block_Widget_Grid_Container {
    protected $_addButtonLabel = 'Add New Partner';

    public function __construct()
    {
//        parent::__construct();
        $this->_controller = 'adminhtml_partner';
        $this->_blockGroup = 'planet';
        $this->_headerText = Mage::helper('planet')->__('Partner');
    }

    protected function _prepareLayout()
    {
        $this->setChild( 'grid',
            $this->getLayout()->createBlock( $this->_blockGroup.'/' . $this->_controller . '_grid',
                $this->_controller . '.grid')->setSaveParametersInSession(true) );
        return parent::_prepareLayout();
    }
}