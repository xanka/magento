<?php
/**
 * Author : Nguyen Trung Hieu
 * Email : hieunt@smartosc.com
 * Date: 9/18/12
 * Time: 11:30 AM
 */
require_once('Mage/Adminhtml/controllers/Report/ProductController.php');
class SM_Vendors_Adminhtml_Report_ProductController extends Mage_Adminhtml_Report_ProductController {
    public function viewedAction() {
        $this->_title($this->__('Reports'))
            ->_title($this->__('Products'))
            ->_title($this->__('Most Viewed'));

        $this->_initAction()
            ->_setActiveMenu('report/product/viewed')
            ->_addBreadcrumb(Mage::helper('reports')->__('Most Viewed'), Mage::helper('reports')->__('Most Viewed'))
            ->_addContent($this->getLayout()->createBlock('adminhtml/report_product_viewed'))
            ->renderLayout();
    }
}