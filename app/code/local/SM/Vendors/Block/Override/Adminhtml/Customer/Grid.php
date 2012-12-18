<?php
class SM_Vendors_Block_Override_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Customer_Grid
{
    protected function _prepareCollection()
    {
		$collection = Mage::getResourceModel('customer/customer_collection')
			->addNameToSelect()
			->addAttributeToSelect('email')
			->addAttributeToSelect('created_at')
			->addAttributeToSelect('group_id')
			->addAttributeToSelect('vendor_customer_group')
			->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
			->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
			->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
			->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
			->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left')
			->joinAttribute('billing_company', 'customer_address/company', 'default_billing', null, 'left')
			->joinAttribute('billing_street', 'customer_address/street', 'default_billing', null, 'left')
			->addExpressionAttributeToSelect('billing_address','CONCAT({{billing_street}}, " , ", {{billing_city}} , " , " , {{billing_country_id}})', array('billing_street', 'billing_city','billing_country_id'))
			;
		
        if($vendor = Mage::helper('smvendors')->getVendorLogin()){
            $collection->joinField('vendor_customer', 'smvendors/vendor_customer', null, 'customer_id=entity_id', "{{table}}.vendor_id={$vendor->getId()}", 'inner');
		}
		
		Mage::dispatchEvent('smvendors_customer_grid_prepare_collection', array('customer_collection' => $collection));
		
		$this->setCollection($collection);
		$grandParent = $this->getGrandParent();
		
		return call_user_func(array($grandParent, '_prepareCollection'));
    }
	
	public function getGrandParent(){
		$grandParent = get_parent_class(get_parent_class($this));
		return $grandParent;
	}
	
	protected function _prepareColumns()
	{
	    parent::_prepareColumns();
	 	if($vendor = Mage::helper('smvendors')->getVendorLogin()){
	    	$this->removeColumn('action');
	    	
	    	
	    }
	    $this->removeColumn('entity_id');
	    $this->removeColumn('billing_country_id');
	    $this->removeColumn('group');
	    $this->addColumn('group', array(
	            'header'    => Mage::helper('customer')->__('Group'),
	            'width'     => '100',
	    		'renderer' => 'smvendors/override_adminhtml_customer_grid_column_renderer_customGroup',  
	            'index'     => 'vendor_customer_group',
        	));
	    $this->addColumn('billing_company', array(
	            'header'    => Mage::helper('customer')->__('Company Name'),
	            'width'     => '100',  
	            'index'     => 'billing_company',
        	));
        $this->addColumn('billing_adress', array(
	            'header'    => Mage::helper('customer')->__('Address'),
	            'width'     => '100',  
	            'index'     => 'billing_address',
        	));

	    $nameColumn = $this->getColumn('name');
	    $nameColumn->setHeader('Contact Person Name');
	    
	    $groups = Mage::getResourceModel('customer/group_collection')
	    ->addFieldToFilter('customer_group_id', array('gt'=> 0))
	    ->addFieldToFilter('vendor_id', 0)
	    ->load()
	    ->toOptionHash();
	    	    
	    $groupColumn = $this->getColumn('group');
	    
	    $groupColumn->setData('options', $groups);
	    
	    Mage::dispatchEvent('smvendors_customer_grid_prepare_columns', array('customer_grid' => $this));
	    
	    $this->addColumnsOrder('action','buyer_prefix');
	    $this->addColumnsOrder('customer_since','buyer_prefix');
	    
	    $this->sortColumnsByOrder();
	    
	    return $this;
	}

	protected function _prepareMassaction()
	{
	    // Do not allow vendor to change customer information
	    if($vendor = Mage::helper('smvendors')->getVendorLogin()){
	    	$this->setMassactionIdField('entity_id');
        	$this->getMassactionBlock()->setFormFieldName('customer');

	        $groups = Mage::getSingleton('eav/config')
                ->getAttribute('customer', 'vendor_customer_group')
                ->getSource()->getAllOptions();

	        array_unshift($groups, array('label'=> '', 'value'=> ''));
	        $this->getMassactionBlock()->addItem('assign_group', array(
	             'label'        => Mage::helper('customer')->__('Assign a Customer Group'),
	             'url'          => $this->getUrl('adminhtml/vendors_customer/massAssignVendorGroup'),
	             'additional'   => array(
	                'visibility'    => array(
	                     'name'     => 'vendor_group',
	                     'type'     => 'select',
	                     'class'    => 'required-entry',
	                     'label'    => Mage::helper('customer')->__('Group'),
	                     'values'   => $groups
	                 )
	            )
	        ));
	        
	        return $this;
	    } else {
	        return parent::_prepareMassaction();
	    }
	}	
	public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id'=>$row->getId()));
    	if($vendor = Mage::helper('smvendors')->getVendorLogin()){
    		//return '';
        }
        else{
        	
        	return $this->getUrl('*/*/edit', array('id'=>$row->getId()));
        }
    }
}
