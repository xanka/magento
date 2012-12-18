<?php

require_once("Mage/Adminhtml/controllers/Report/SalesController.php");

class SM_Vendors_Adminhtml_Vendors_ReportsController extends SM_Vendors_Controller_Adminhtml_Action {

    public function _initAction() {
        $act = $this->getRequest()->getActionName();
        if (!$act) {
            $act = 'default';
        }
        $this->loadLayout()
                ->_addBreadcrumb(Mage::helper('smvendors')->__('Reports'), Mage::helper('smvendors')->__('Reports'))
                ->_addBreadcrumb(Mage::helper('smvendors')->__('Vendor Orders Report'), Mage::helper('smvendors')->__('Vendor Orders Report'));
        return $this;
    }

    public function _initReportAction($blocks) {
        if (!is_array($blocks)) {
            $blocks = array($blocks);
        }

        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('filter'));

        $requestData = $this->_filterDates($requestData, array('from', 'to'));
        $requestData['store_ids'] = $this->getRequest()->getParam('store_ids');
        $params = new Varien_Object();

        foreach ($requestData as $key => $value) {
            if (!empty($value)) {
                $params->setData($key, $value);
            }
        }

        foreach ($blocks as $block) {
            if ($block) {
                $block->setPeriodType($params->getData('period_type'));
                $block->setFilterData($params);
            }
        }
        return $this;
    }

    public function vendorordersAction() {
        $this->_title($this->__('Reports'))->_title($this->__('Advanced Reports'))->_title($this->__('Orders Delivery Report'));

        $this->_showLastExecutionTime(Mage_Reports_Model_Flag::REPORT_ORDER_FLAG_CODE, 'sales');

        $this->_initAction()
                ->_setActiveMenu('smvendors/vendororders')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Orders Delivery Report'), Mage::helper('adminhtml')->__('Orders Delivery Report'));
        $gridBlock = $this->getLayout()->getBlock('adminhtml_reports_vendorOrders.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');
        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));
        $this->renderLayout();
    }

    /**
     * Export bestsellers report grid to CSV format
     */
    public function exportVendorOrdersCsvAction() {
        $fileName = 'vendor_orders.csv';
        $grid = $this->getLayout()->createBlock('smvendors/adminhtml_reports_vendorOrders_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     * Export bestsellers report grid to Excel XML format
     */
    public function exportVendorOrdersExcelAction() {
        $fileName = 'vendor_orders.xls';
        $grid = $this->getLayout()->createBlock('smvendors/adminhtml_reports_vendorOrders_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    /**
     * Retrieve array of collection names by code specified in request
     *
     * @deprecated after 1.4.0.1
     * @return array
     */
    protected function _getCollectionNames() {
        return array();
    }

    protected function _showLastExecutionTime($flagCode, $refreshCode) {
        $flag = Mage::getModel('reports/flag')->setReportFlagCode($flagCode)->loadSelf();
        $updatedAt = ($flag->hasData()) ? Mage::app()->getLocale()->storeDate(
                        0, new Zend_Date($flag->getLastUpdate(), Varien_Date::DATETIME_INTERNAL_FORMAT), true
                ) : 'undefined';

        $refreshStatsLink = $this->getUrl('*/*/refreshstatistics');
        $directRefreshLink = $this->getUrl('*/*/refreshRecent', array('code' => $refreshCode));

        Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('adminhtml')->__('Last updated: %s. To refresh last day\'s <a href="%s">statistics</a>, click <a href="%s">here</a>.', $updatedAt, $refreshStatsLink, $directRefreshLink));
        return $this;
    }

    /**
     * Refresh statistics for last 25 hours
     *
     * @deprecated after 1.4.0.1
     * @return Mage_Adminhtml_Report_SalesController
     */
    public function refreshRecentAction() {
        return $this->_forward('refreshRecent', 'report_statistics');
    }

    /**
     * Refresh statistics for all period
     *
     * @deprecated after 1.4.0.1
     * @return Mage_Adminhtml_Report_SalesController
     */
    public function refreshLifetimeAction() {
        return $this->_forward('refreshLifetime', 'report_statistics');
    }

    /**
     * @deprecated after 1.4.0.1
     */
    public function refreshStatisticsAction() {
        return $this->_forward('index', 'report_statistics');
    }

    protected function _isAllowed() {
        return $this->_getSession()->isAllowed('report/smvendors/vendororders');
    }

    /**
     * Retrieve admin session model
     *
     * @return Mage_Admin_Model_Session
     */
    protected function _getSession() {
        if (is_null($this->_adminSession)) {
            $this->_adminSession = Mage::getSingleton('admin/session');
        }
        return $this->_adminSession;
    }

    public function getExcelFile($sheetName = '', $content = '') {
        require_once('simple_html_dom.php');
        //$content = strip_tags($content , '<table><tr><td><thead><tbody><br><th><hr>');
        //$content = str_replace('<hr style="border-color:#DADFE0; border-style: solid; border-width: 0 1px 1px 0;"/>',"<br/>",$content);

        $content = str_replace("\n", "<br/>", $content);

        $html = str_get_html($content);
        $table = $html->find('div[class=grid]', 0);
        $content = $table->innertext;

        $content = str_replace('<hr style="border-color:#DADFE0; border-style: solid; border-width: 0 1px 1px 0;"/>', "<br/>", $content);

        $io = new Varien_Io_File();

        $path = Mage::getBaseDir('var') . DS . 'export' . DS;
        $name = md5(microtime());
        $file = $path . DS . $name . '.xls';

        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
        $io->streamOpen($file, 'w+');
        $io->streamLock(true);
        $io->streamWrite($content);
        $io->streamUnlock();
        $io->streamClose();

        return array(
            'type' => 'filename',
            'value' => $file,
            'rm' => true // can delete file after use
        );
    }

}
