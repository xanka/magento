<?php

class SM_Reviews_Block_Adminhtml_Reviews_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('review_info_tabs');
        $this->setDestElementId('review_edit_form');
        $this->setTitle(Mage::helper('adminhtml')->__('Review Information'));
    }

//     protected function _prepareLayout()
//     {
//         $review = Mage::registry('current_review');

//         $this->addTab('info', $this->getLayout()->createBlock('smpackages/adminhtml_packages_edit_info')->setReview($review)->setActive(true));

//         return parent::_prepareLayout();
//     }

    public function getHeaderText()
    {
 		
    	return Mage::helper('smreviews')->__("Customer Reviews");
    }
}