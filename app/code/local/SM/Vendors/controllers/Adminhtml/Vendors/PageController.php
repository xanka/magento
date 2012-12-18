<?php

class SM_Vendors_Adminhtml_Vendors_PageController extends SM_Vendors_Controller_Adminhtml_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('smvendors');

        return $this;
    }

    public function indexAction() {
        $this->_title($this->__('Page Manager'));
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('smvendors/adminhtml_page'));
        $this->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {
        $pageId = $this->getRequest()->getParam('id');
        $_model = Mage::getModel('smvendors/page')->load($pageId);

        //if ($_model->getId()) {
        $this->_title($_model->getId() ? $_model->getName() : $this->__('New Page'));

        Mage::register('page_data', $_model);
        Mage::register('current_page', $_model);

        $this->_initAction();
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Page Manager'), Mage::helper('adminhtml')->__('Page Manager'), $this->getUrl('*/*/'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Edit Page'), Mage::helper('adminhtml')->__('Edit Page'));

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->_addContent($this->getLayout()->createBlock('smvendors/adminhtml_page_edit'))
                ->_addLeft($this->getLayout()->createBlock('smvendors/adminhtml_page_edit_tabs'));

        $this->renderLayout();
        //} else {
        //Mage::getSingleton('adminhtml/session')->addError(Mage::helper('smvendors')->__('The page does not exist.'));
        //$this->_redirect('*/*/');
        //}
    }

    public function saveAction() {
        $request = $this->getRequest()->getParams();
        var_dump($request['applyforall']);

        if ($data = $this->getRequest()->getPost()) {
            $_model = Mage::getModel('smvendors/page');
            if ($this->getRequest()->getParam('id')) {

                $_model->setData($data)
                        ->setId($this->getRequest()->getParam('id'));
            } else {
                $_model->setData($data);
            }
            try {
                $_model->save();
                var_dump($data);

                // apply for all pape if admin tick on apply change for all page
                if (!is_null($data['applyforall'])) {
                    $pages = Mage::getModel('smvendors/page')->getCollection();

                    foreach ($pages as $_page) {
                        if ($_page->getData('identifier') == $data['identifier']) {
                            $_page->setData('title', $data['title']);
                            $_page->save();
                        }
                    }
                }

                /* Ecommage: Apply content to all vendor */

                if (!is_null($data['applycontentforall'])) {
                    $pages = Mage::getModel('smvendors/page')->getCollection();

                    foreach ($pages as $_page) {
                        if ($_page->getData('identifier') == $data['identifier']) {
                            $_page->setData('content', $data['content']);
                            $_page->save();
                        }
                    }
                }


                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('smvendors')->__('Page was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $_model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('smvendors')->__('Unable to find page to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('smvendors/page');

                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Banner was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $IDList = $this->getRequest()->getParam('page');
        if (!is_array($IDList)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select page(s)'));
        } else {
            try {
                foreach ($IDList as $itemId) {
                    $_model = Mage::getModel('smvendors/page')
                                    ->setIsMassDelete(true)->load($itemId);
                    $_model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                                'Total of %d record(s) were successfully deleted', count($IDList)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction() {
        $IDList = $this->getRequest()->getParam('page');
        if (!is_array($IDList)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select page(s)'));
        } else {
            try {
                foreach ($IDList as $itemId) {
                    $_model = Mage::getSingleton('smvendors/page')
                            ->setIsMassStatus(true)
                            ->load($itemId)
                            ->setIsActive($this->getRequest()->getParam('status'))
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($IDList))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function imageAction() {
        $result = array();
        try {
            $uploader = new My_Ipage_Media_Uploader('image');
            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save(
                    Mage::getSingleton('smvendors/config')->getBaseMediaPath()
            );

            $result['url'] = Mage::getSingleton('smvendors/config')->getMediaUrl($result['file']);
            $result['cookie'] = array(
                'name' => session_name(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain()
            );
        } catch (Exception $e) {
            $result = array('error' => $e->getMessage(), 'errorcode' => $e->getCode());
        }

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function categoriesJsonAction() {
        $pageId = $this->getRequest()->getParam('id');
        $_model = Mage::getModel('smvendors/page')->load($pageId);
        Mage::register('page_data', $_model);
        Mage::register('current_page', $_model);

        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('smvendors/adminhtml_page_edit_tab_category')
                        ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }

    /**
     * Add an extra title to the end or one from the end, or remove all
     *
     * Usage examples:
     * $this->_title('foo')->_title('bar');
     * => bar / foo / <default title>
     *
     * $this->_title()->_title('foo')->_title('bar');
     * => bar / foo
     *
     * $this->_title('foo')->_title(false)->_title('bar');
     * bar / <default title>
     *
     * @see self::_renderTitles()
     * @param string|false|-1|null $text
     * @return Mage_Core_Controller_Varien_Action
     */
    protected function _title($text = null, $resetIfExists = true) {
        if (is_string($text)) {
            $this->_titles[] = $text;
        } elseif (-1 === $text) {
            if (empty($this->_titles)) {
                $this->_removeDefaultTitle = true;
            } else {
                array_pop($this->_titles);
            }
        } elseif (empty($this->_titles) || $resetIfExists) {
            if (false === $text) {
                $this->_removeDefaultTitle = false;
                $this->_titles = array();
            } elseif (null === $text) {
                $this->_removeDefaultTitle = true;
                $this->_titles = array();
            }
        }
        return $this;
    }

}