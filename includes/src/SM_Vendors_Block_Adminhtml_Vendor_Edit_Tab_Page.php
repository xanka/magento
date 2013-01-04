<?php
class SM_Vendors_Block_Adminhtml_Vendor_Edit_Tab_Page extends Mage_Adminhtml_Block_Widget
{
    protected $_product = null;
    public function __construct()
    {
        parent::__construct();
        $this->setSkipGenerateContent(true);
        $this->setTemplate('sm/vendors/edit/page/content.phtml');
    }
    public function getTabClass()
    {
        return 'ajax';
    }

    protected function _prepareLayout()
    {
        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('smvendors')->__('Create New Page'),
                    'class' => 'add',
                    'id'    => 'create_new_page',
                    'on_click' => 'vendorPages.add()'
                ))
        );
        return parent::_prepareLayout();
    }

    /**
     * Check block readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return $this->getProduct()->getCompositeReadonly();
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }
    public function getProduct()
    {
        return Mage::registry('product');
    }
    public function canShowTab()
    {
        return true;
    }
    public function isHidden()
    {
        return false;
    }
	public function getAllPages(){
		$customer = $this->_getCustomer();
		$pages = array();
		if(!empty($customer)){
			$vendor = $customer->getVendorModel();
			if(!empty($vendor)){
				$pageCollection = $vendor->getStaticPages();
				foreach($pageCollection as $page){
					$pages[] = $page->getData();				
				}
				
			}
		}
		return $pages;
	}
	
	protected function _getCustomer(){
		return Mage::registry('current_customer');
	}
}
