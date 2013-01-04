<?php


class SM_Reviews_Adminhtml_Vendors_ReviewsController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('smvendors/reviews');

        return $this;
    }

    /**
     * Index action
     *
     */
    public function indexAction() {
        $this->_title($this->__('All reviews'));
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('smreviews/adminhtml_reviews'));
        $this->renderLayout();
    }
    
    
    /**
     * AJAX grid action
     *
     */    
    public function gridAction() {
    	$this->loadLayout();
//         $this->getResponse()->setBody($this->getLayout()->getBlock('adminhtml.permission.review.grid')->toHtml());
        $this->getResponse()->setBody($this->getLayout()->createBlock('smreviews/adminhtml_reviews_grid')->toHtml());
        
    }
    protected function _initReview($requestVariable = 'id')
    {
        $this->_title($this->__('Vendors'))
             ->_title($this->__('Customer Reviews'));

        $review = Mage::getModel('smreviews/reviews')->load($this->getRequest()->getParam($requestVariable));

        Mage::register('current_review', $review);
        return Mage::registry('current_review');
    }    
    
    /**
     * Edit configuration section
     *
     */
    public function editAction() {
        $review = $this->_initReview();
        $this->_initAction();
            $breadCrumb      = $this->__('Edit Review');
            $breadCrumbTitle = $this->__('Edit Review');

        $this->_title($review->getReviewId());

        $this->_addBreadcrumb($breadCrumb, $breadCrumbTitle);

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->_addContent(
            $this->getLayout()->createBlock('smreviews/adminhtml_reviews_buttons')
                ->setReviewId($review->getReviewId())
                ->setReview($review)
                ->setTemplate('sm/reviews/reviewinfo.phtml')
        );

        $this->_addContent(
            $this->getLayout()->createBlock('smreviews/adminhtml_reviews_edit_info')
                ->setReview($review)
//                ->setTemplate('permissions/roleinfo.phtml')
        );        
        $this->renderLayout();
    }
    public function massDeleteAction()
    {
        $reviewIds = $this->getRequest()->getParam('review');
        if (!is_array($reviewIds)) {
            $this->_getSession()->addError($this->__('Please select item(s).'));
        } else {
            if (!empty($reviewIds)) {
                try {
                    foreach ($reviewIds as $reviewId) {
                        $review = Mage::getSingleton('smreviews/reviews')->load($reviewId);
                        $review->delete();
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($reviewIds))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Update product(s) status action
     *
     */
    public function massStatusAction()
    {
        $reviewIds = (array)$this->getRequest()->getParam('review');
        $status     = (int)$this->getRequest()->getParam('status');

        if (!is_array($reviewIds)) {
            $this->_getSession()->addError($this->__('Please select item(s).'));
        } else {
            if (!empty($reviewIds)) {
                try {
                    foreach ($reviewIds as $reviewId) {
                        $review = Mage::getSingleton('smreviews/reviews')->load($reviewId);
                        $review->setStatus($status)->save();
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been saved.', count($reviewIds))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }

        $this->_redirect('*/*/index');
    }


    /**
     * Save configuration
     *
     */
    public function saveAction() {
        $session = Mage::getSingleton('adminhtml/session');
        /* @var $session Mage_Adminhtml_Model_Session */

        try {
            $reviewId = $this->getRequest()->getParam('review_id', false);
            //$comment = $this->getRequest()->getParam('comment', '');
            //$rating = $this->getRequest()->getParam('rating', 5);
            //$status = $this->getRequest()->getParam('status', SM_Reviews_Model_Reviews_Status::STATUS_ENABLED);

            $data = $this->getRequest()->getPost();
            
            if($reviewId) {
            	$reviewModel = Mage::getModel('smreviews/reviews')->load($reviewId);
            	if($reviewModel->getId()) {
            		$reviewModel
            			->addData($data)
            			//->setComment($comment)
            			//->setRating(intval($rating))
            			//->setStatus(intval($status))
            			->save();
            		$session->addSuccess(Mage::helper('adminhtml')->__('The review has been saved.'));
            	} else {
            		$session->addError(Mage::helper('adminhtml')->__('The review not existed anymore.'));
            	}
            }

        } catch (Mage_Core_Exception $e) {
            foreach (explode("\n", $e->getMessage()) as $message) {
                $session->addError($message);
            }
        } catch (Exception $e) {
            $session->addException($e, Mage::helper('adminhtml')->__('An error occurred while saving this review:') . ' '
                    . $e->getMessage());
        }

        $this->_saveState($this->getRequest()->getPost('config_state'));

        $this->_redirect('*/*/index');
    }

    /**
     *  Advanced save procedure
     */
    protected function _saveAdvanced() {
        Mage::app()->cleanCache(
                array(
                    'layout',
                    Mage_Core_Model_Layout_Update::LAYOUT_GENERAL_CACHE_TAG
        ));
    }

    /**
     * Save fieldset state through AJAX
     *
     */
    public function stateAction() {
        if ($this->getRequest()->getParam('isAjax') == 1
                && $this->getRequest()->getParam('container') != ''
                && $this->getRequest()->getParam('value') != '') {

            $configState = array(
                $this->getRequest()->getParam('container') => $this->getRequest()->getParam('value')
            );
            $this->_saveState($configState);
            $this->getResponse()->setBody('success');
        }
    }


    /**
     * Check is allow modify system configuration
     *
     * @return bool
     */
    protected function _isAllowed() {
        return true;
        // TODO implement permission check here
        //return Mage::getSingleton('admin/session')->isAllowed('system/config');
    }

    /**
     * Check if specified section allowed in ACL
     *
     * Will forward to deniedAction(), if not allowed.
     *
     * @param string $section
     * @return bool
     */
    protected function _isSectionAllowed($section) {
        return true;
    }

    /**
     * Save state of configuration field sets
     *
     * @param array $configState
     * @return bool
     */
    protected function _saveState($configState = array()) {
        $adminUser = Mage::getSingleton('admin/session')->getUser();
        if (is_array($configState)) {
            $extra = $adminUser->getExtra();
            if (!is_array($extra)) {
                $extra = array();
            }
            if (!isset($extra['configState'])) {
                $extra['configState'] = array();
            }
            foreach ($configState as $fieldset => $state) {
                $extra['configState'][$fieldset] = $state;
            }
            $adminUser->saveExtra($extra);
        }

        return true;
    }

}
