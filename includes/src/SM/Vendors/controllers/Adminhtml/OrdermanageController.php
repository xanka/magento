<?php
/**
 * Date: 10/21/12
 * Time: 1:18 AM
 */

class SM_Vendors_Adminhtml_OrdermanageController extends SM_Vendors_Controller_Adminhtml_Customer {
    public function preDispatch() {

    }

    public function indexAction() {
        $content = $this->getLayout()->createBlock('core/template')->setTemplate('order/manage.phtml')->toHtml();
        echo $content;
    }

    public function resultAction() {
        $param = $this->getRequest()->getParams();
        $orderId = $param['id'];

        if ($orderId > 0) {

            $result = $this->getLayout()->createBlock('core/template')->setTemplate('order/manage.phtml')->toHtml();
        }
    }
}