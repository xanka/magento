<?php
/**
 * Date: 10/27/12
 * Time: 3:07 AM
 */

class SM_Planet_Block_Adminhtml_Partner_Grid extends  Mage_Adminhtml_Block_Widget_Grid {
    public function __construct()
    {
        parent::__construct();
        $this->setId('partner_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('planet/partner')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('planet')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'id',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('planet')->__('Company Name'),
            'align'     =>'left',
            'index'     => 'company_name',
        ));

        $this->addColumn('description', array(
            'header'    => Mage::helper('planet')->__('Website'),
            'align'     =>'left',
            'index'     => 'website',
        ));

        $this->addColumn('other', array(
            'header'    => Mage::helper('planet')->__('Image'),
            'align'     => 'left',
            'index'     => 'image_url',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}