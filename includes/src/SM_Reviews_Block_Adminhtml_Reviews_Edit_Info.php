<?php

class SM_Reviews_Block_Adminhtml_Reviews_Edit_Info extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    public function getTabLabel() {
        return Mage::helper('adminhtml')->__('Review Info');
    }

    public function getTabTitle() {
        return $this->getTabLabel();
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }

    public function _beforeToHtml() {
        $this->_initForm();

        return parent::_beforeToHtml();
    }

    protected function _initForm() {
        $reviewId = $this->getRequest()->getParam('id');

        $form = new Varien_Data_Form(array(
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/save', array()),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ));

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('adminhtml')->__('Review Information')));
        $fieldset->addField('order_id', 'label', array(
            'name' => 'order_id',
            'label' => Mage::helper('adminhtml')->__('Order #'),
            'id' => 'order_id',
//            'class' => 'required-entry',
//            'required' => true,
                )
        );
        $fieldset->addField('customer_name', 'label', array(
            'name' => 'customer_name',
            'label' => Mage::helper('adminhtml')->__('Customer Name'),
            'id' => 'customer_name',
                )
        );
        $fieldset->addField('customer_email', 'label', array(
            'name' => 'customer_email',
            'label' => Mage::helper('adminhtml')->__('Customer Email'),
            'id' => 'customer_email',
                )
        );
        $vendorName = $fieldset->addField('vendor_name', 'label', array(
            'name' => 'vendor_name',
            'label' => Mage::helper('adminhtml')->__('Vendor Name'),
            'id' => 'vendor_name',
                )
        );
        //$fieldset->addType('rating', 'SM_Reviews_Block_Adminhtml_Reviews_Edit_Rating');

        if($vendor = Mage::helper('smvendors')->getVendorLogin()){
        	$fieldset->addField('status', 'label', array(
        			'name' => 'status',
        			'label' => Mage::helper('adminhtml')->__('Status'),
        			'id' => 'status'
        	)
        	);
        	
        	$fieldset->addField('rating', 'label', array(
        			'name' => 'rating',
        			'label' => Mage::helper('adminhtml')->__('Rating'),
        			'id' => 'rating',
        	)
        	);
        	$fieldset->addField('comment', 'label', array(
        			'name' => 'comment',
        			'label' => Mage::helper('adminhtml')->__('Comment'),
        			'id' => 'comment',
        	)
        	);
        }
        else{
        	$fieldset->addField('status', 'select', array(
        			'name' => 'status',
        			'label' => Mage::helper('adminhtml')->__('Status'),
        			'id' => 'status',
        			'values' => Mage::getSingleton('smreviews/reviews_status')->getOptionArray()
        	)
        	);
        	 
        	$fieldset->addField('rating', 'select', array(
        			'name' => 'rating',
        			'label' => Mage::helper('adminhtml')->__('Rating'),
        			'id' => 'rating',
        			'values' => array(
        					'1' => '1 Star',
        					'2' => '2 Stars',
        					'3' => '3 Stars',
        					'4' => '4 Stars',
        					'5' => '5 Stars',
        			),
        	)
        	);
        	$fieldset->addField('comment', 'textarea', array(
        			'name' => 'comment',
        			'label' => Mage::helper('adminhtml')->__('Comment'),
        			'id' => 'comment',
        	)
        	);
        }
        
        if(Mage::getSingleton('admin/session')->isAllowed('smvendors/package_vendor_reply_review')){
        	$fieldset->addField('vendor_reply', 'textarea', array(
	             'name' => 'vendor_reply',
	             'label' => Mage::helper('adminhtml')->__('Reply'),
	             'id' => 'vendor_reply',
	             'required' => true,
	           )
	         );
        }
         
        $fieldset->addField('review_id', 'hidden', array(
            'name' => 'review_id',
            'id' => 'review_id',
                )
        );
        $data = $this->getReview()->getData();
        if(isset($data['vendor_id'])) {
        	$_vendor = Mage::getModel('smvendors/vendor')->load($data['vendor_id']);
        	if($_vendor->getId()) {
        		$data['vendor_name'] = $_vendor->getVendorName();
        	}
        }
        $form->setValues($data);
        $form->setUseContainer(true);
        $this->setForm($form);
    }

}