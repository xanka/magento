<?php

class SM_Vendors_Block_Adminhtml_Catalog_Product_Render_Vendor
    extends Varien_Data_Form_Element_Abstract
{
    /**
     * Retrieve Element HTML fragment
     *
     * @return string
     */
    public function getElementHtml()
    {
     
		$html = array();
		$form_key = Mage::getSingleton('adminhtml/url')->getSecretKey("adminhtml_catalog_category","index");
		$_product = $this->getProduct();
		$vendor = Mage::helper('smvendors')->getVendorLogin();
		if(!empty($_product)){
			if(!$vendor){
				$html[]='<select class="required-entry" name="product['.$this->getHtmlId().']">'.
				$vendorCollection = $this->getVendorCollection();
				$html[] = '<option value="">'.Mage::helper('smvendors')->__('Select vendor').'</option>';
				foreach($vendorCollection as $vendor){
				
					$selected = '';
					if($vendor->getVendorId() == $this->getValue()){
						$selected = 'selected="selected"';
					}
					$html[] = '<option '.$selected.' value="'.$vendor->getVendorId().'">'.$vendor->getVendorName().'</option>';
				}
				$html[] ='</select>';
			}
			else{
				$html[] ='<input type="hidden" name="product['.$this->getHtmlId().']" value="'.$vendor->getVendorId().'"/>';
			}
		}
		else{
			if(!$vendor){
				$html[]='<select id="'.$this->getHtmlId().'" name="attributes['.$this->getHtmlId().']">'.
					$vendorCollection = $this->getVendorCollection();
					$html[] = '<option value="">'.Mage::helper('smvendors')->__('Select vendor').'</option>';
					foreach($vendorCollection as $vendor){
					
						$selected = '';
						if($vendor->getVendorId() == $this->getValue()){
							$selected = 'selected="selected"';
						}
						$html[] = '<option '.$selected.' value="'.$vendor->getId().'">'.$vendor->getVendorName().'</option>';
					}
				$html[] ='</select>';
				$html[] = '<span class="attribute-change-checkbox"><input type="checkbox" onclick="toogleFieldEditMode(this, \''.$this->getHtmlId().'\')" id="'.$this->getHtmlId().'-checkbox"><label for="'.$this->getHtmlId().'-checkbox">Change</label></span>';
				$html[] = '<script type="text/javascript">initDisableFields(\''.$this->getHtmlId().'\')</script>';
			}
			else{
				$html[] ='<input type="hidden" name="attributes['.$this->getHtmlId().']" value="'.$vendor->getVendorId().'"/>';
			}
		}
        return implode("\n",$html);
    }
	
	public function getProduct(){
		return Mage::registry('current_product');
	}
	
	public function getVendorCollection(){
		$collection = Mage::getResourceModel('smvendors/vendor_collection');
		return $collection;
	}
}
