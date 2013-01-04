<?php


class SM_Dropship_Block_Adminhtml_Shipping_Edit_Tab_Vendor extends Mage_Adminhtml_Block_Widget_Grid
{

	public function __construct()
    {
        parent::__construct();
        $this->setId('shipping_vendors');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'related_vendor') {
            $vendorIds = $this->_getSelectedVendors();
            if (empty($vendorIds)) {
                $vendorIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('vendor_id', array('in'=>$vendorIds));
            }
            elseif(!empty($vendorIds)) {
                $this->getCollection()->addFieldToFilter('vendor_id', array('nin'=>$vendorIds));
            }
        }
        else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('smvendors/vendor')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('related_vendor', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'        => 'related_vendor',
            'values'    => $this->_getSelectedVendors(),
            'align'     => 'center',
            'index'     => 'vendor_id'
        ));
        $this->addColumn('id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => '60px',
            'index'     => 'vendor_id'
        ));
        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'vendor_name'
        ));

		
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/gridvendor', array('_current'=>true));
    }
	
	protected function _getRate()
    {
        return Mage::registry('current_rate');
    }

    protected function _getSelectedVendors()
    {
        $vendors = $this->getRequest()->getPost('selected_vendors');
        if (is_null($vendors)) {
            $collection = $this->_getRate()->getVendorIds();
			if(!empty($collection)){
				foreach ($collection as $vendor) {
					$vendors[$vendor] = $vendor;
	            }
			}
        }
        return $vendors;
    }
    
	public function getVendorsJson()
    {
        $result = $this->_getSelectedVendors();
		$vendors = array();
		if(!empty($result)){
			foreach($result as $row){
				$vendors[$row]=$row;
			}
	        if (!empty($vendors)) {
	            return Mage::helper('core')->jsonEncode($vendors);
	        }
		}
        return '{}';
    }

    public function isAjax()
    {
        return Mage::app()->getRequest()->isXmlHttpRequest() || Mage::app()->getRequest()->getParam('isAjax');
    }
    
   	protected function _toHtml(){
   		$scriptBlock = $this->getLayout()->createBlock('core/template')->setTemplate('sm/dropship/shipping/edit/tab/vendor.phtml')->setData('vendor_block',$this)->toHtml();
   		$html = parent::_toHtml().$scriptBlock;
   		return $html;
   	}
}
