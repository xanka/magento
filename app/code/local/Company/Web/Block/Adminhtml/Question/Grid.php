<?php

class Company_Web_Block_Adminhtml_Question_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('questionGrid');
      $this->setDefaultSort('id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('web/question')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('id', array(
          'header'    => Mage::helper('web')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'id',
      ));

      $this->addColumn('question', array(
          'header'    => Mage::helper('web')->__('Question'),
          'align'     =>'left',
          'index'     => 'question',
      ));

      $this->addColumn('status', array(
          'header'    => Mage::helper('web')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              0 => 'Disabled',
          ),
      ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('web')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('web')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
//        $this->setMassactionIdField('id');
//        $this->getMassactionBlock()->setFormFieldName('web');
//
//        $this->getMassactionBlock()->addItem('delete', array(
//             'label'    => Mage::helper('web')->__('Delete'),
//             'url'      => $this->getUrl('*/*/massDelete'),
//             'confirm'  => Mage::helper('web')->__('Are you sure?')
//        ));
//
//        $statuses = Mage::getSingleton('web/status')->getOptionArray();
//
//        array_unshift($statuses, array('label'=>'', 'value'=>''));
//        $this->getMassactionBlock()->addItem('status', array(
//             'label'=> Mage::helper('web')->__('Change status'),
//             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
//             'additional' => array(
//                    'visibility' => array(
//                         'name' => 'status',
//                         'type' => 'select',
//                         'class' => 'required-entry',
//                         'label' => Mage::helper('web')->__('Status'),
//                         'values' => $statuses
//                     )
//             )
//        ));
//        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}