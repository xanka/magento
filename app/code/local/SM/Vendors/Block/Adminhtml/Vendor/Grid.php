<?php

class SM_Vendors_Block_Adminhtml_Vendor_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('vendorGrid');
		$this->setUseAjax(true);
        $this->setDefaultSort('entity_id');
        $this->setSaveParametersInSession(true);
    }
    
	protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id')
			->addAttributeToFilter('customer_type','vendor')
            //->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            //->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            //->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
           // ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            //->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left')
			->joinTable('smvendors/vendor', 'customer_id=entity_id', 
			        array('vendor_name' => 'vendor_name',
			              'vendor_logo'=>'vendor_logo',
			              'vendor_status' => 'vendor_status',
			              'vendor_prefix' => 'vendor_prefix',
		                ));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
		
	   $this->addColumn('entity_id', array(
            'header'    => Mage::helper('customer')->__('ID'),
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'  => 'number',
        ));
        $this->addColumn('vendor_prefix', array(
                'header'    => Mage::helper('smvendors')->__('Merchant Prefix'),
                'width'     => '100px',
                'index'     => 'vendor_prefix'
        ));
        
		$this->addColumn('vendor_logo', array(
            'header' => Mage::helper('smvendors')->__('Logo'),
            'align' => 'center',
            'index' => 'vendor_logo',
            'renderer' => 'smvendors/adminhtml_widget_grid_column_renderer_image',
            'escape' => true,
            'sortable' => false,
            'width' => '150px',
        ));
		
		$this->addColumn('vendor_name', array(
            'header'    => Mage::helper('customer')->__('Merchant name'),
//             'width'     => '150',
            'index'     => 'vendor_name'
        ));
		
        $this->addColumn('email', array(
            'header'    => Mage::helper('customer')->__('Email'),
//             'width'     => '150',
            'index'     => 'email'
        ));
		

        $this->addColumn('vendor_status', array(
            'header'    =>  Mage::helper('customer')->__('Merchant Status'),
            'width'     =>  '100',
            'index'     =>  'vendor_status',
            'type'      =>  'options',
            'options'   =>  array(
				1 => Mage::helper('catalog')->__('Active'),
                0 => Mage::helper('catalog')->__('Inactive'),
			),
        ));


      
		
        $this->addColumn('customer_since', array(
            'header'    => Mage::helper('customer')->__('Created at'),
            'type'      => 'datetime',
            'align'     => 'center',
            'index'     => 'created_at',
            'gmtoffset' => true
        ));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('customer')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('customer')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('customer')->__('Excel XML'));
        return $this;
    }
	
	protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('customer');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('customer')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('customer')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('newsletter_subscribe', array(
             'label'    => Mage::helper('customer')->__('Subscribe to Newsletter'),
             'url'      => $this->getUrl('*/*/massSubscribe')
        ));

        $this->getMassactionBlock()->addItem('newsletter_unsubscribe', array(
             'label'    => Mage::helper('customer')->__('Unsubscribe from Newsletter'),
             'url'      => $this->getUrl('*/*/massUnsubscribe')
        ));

        $groups = $this->helper('customer')->getGroups()->toOptionArray();

        array_unshift($groups, array('label'=> '', 'value'=> ''));
        $this->getMassactionBlock()->addItem('assign_group', array(
             'label'        => Mage::helper('customer')->__('Assign a Customer Group'),
             'url'          => $this->getUrl('*/*/massAssignGroup'),
             'additional'   => array(
                'visibility'    => array(
                     'name'     => 'group',
                     'type'     => 'select',
                     'class'    => 'required-entry',
                     'label'    => Mage::helper('customer')->__('Group'),
                     'values'   => $groups
                 )
            )
        ));

        return $this;
    }
	
	public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=> true));
    }

    public function getRowUrl($row)
    {
        if($vendor = Mage::helper('smvendors')->getVendorLogin()){
    		return '';
        }
        else{
        	
        	return $this->getUrl('*/*/edit', array('id'=>$row->getId()));
        }
    }
}