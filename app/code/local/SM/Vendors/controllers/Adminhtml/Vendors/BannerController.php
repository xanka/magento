<?php

class SM_Vendors_Adminhtml_Vendors_BannerController extends SM_Vendors_Controller_Adminhtml_Action {
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('smvendors');

        return $this;
    }

    public function indexAction() {
        $this->_title($this->__('Banner Manager'));
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('smvendors/adminhtml_banner'));
        $this->renderLayout();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {
        $bannerId     = $this->getRequest()->getParam('id');
        $_model  = Mage::getModel('smvendors/banner')->load($bannerId);

        //if ($_model->getId()) {
            $this->_title($_model->getId() ? $_model->getName() : $this->__('New Banner'));

            Mage::register('banner_data', $_model);
            Mage::register('current_banner', $_model);

            $this->_initAction();
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Banner Manager'), Mage::helper('adminhtml')->__('Banner Manager'), $this->getUrl('*/*/'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Edit Banner'), Mage::helper('adminhtml')->__('Edit Banner'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
				$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
			}
            $this->_addContent($this->getLayout()->createBlock('smvendors/adminhtml_banner_edit'))
                    ->_addLeft($this->getLayout()->createBlock('smvendors/adminhtml_banner_edit_tabs'));

            $this->renderLayout();
        //} else {
            //Mage::getSingleton('adminhtml/session')->addError(Mage::helper('smvendors')->__('The banner does not exist.'));
            //$this->_redirect('*/*/');
        //}
    }

    public function saveAction() {
	
        if ($data = $this->getRequest()->getPost()) {
            $_model = Mage::getModel('smvendors/banner');
            if(isset($data['position'])){
            	$data['position'] = implode(',', $data['position']);
            }
            if($this->getRequest()->getParam('id')){
            
            	$_model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));
            }
            else{
            	$_model->setData($data);
            }
            
                $imagedata = array();
	     
	        if (!empty($_FILES['image']['name'])) {
	            try {
	                $ext = substr($_FILES['image']['name'], strrpos($_FILES['image']['name'], '.') + 1);
	                $fname = 'File-' . time() . '.' . $ext;
	                $uploader = new Varien_File_Uploader('image');
	                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png')); // or pdf or anything
	
	                $uploader->setAllowRenameFiles(true);
	                $uploader->setFilesDispersion(false);
	
	                $path = Mage::getBaseDir('media').DS.'vendor'.DS.'banners';
	
	                $uploader->save($path, $fname);
	
	                $imagedata['image'] = 'vendor/banners/'.$fname;
					$_model->setData('image',$imagedata['image']);
	            } catch (Exception $e) {
	               Mage::logException($e);
	            }
	        }
		
			if (empty($imagedata['image'])) {
				$vendorLogo = $this->getRequest()->getPost('image');
	            if (isset($vendorLogo['delete']) && $vendorLogo['delete'] == 1) {
					if ($vendorLogo['value'] != '') {
						$_helper = Mage::helper('smvendors');
						$file = Mage::getBaseDir('media').DS.$_helper->updateDirSepereator($vendorLogo['value']);
						try {
							$io = new Varien_Io_File();
							$result = $io->rmdir($file, true);
						} catch (Exception $e) {
						    Mage::logException($e);
						}
					}
					$imagedata['image']='';
					$_model->setData('image',$imagedata['image']);
				}
				else{
					$_model->setData('image',$vendorLogo['value']);
				}
				
			}
            
            try {
                $_model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('smvendors')->__('Banner was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('smvendors')->__('Unable to find banner to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $model = Mage::getModel('smvendors/banner');

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
        $IDList = $this->getRequest()->getParam('banner');
        if(!is_array($IDList)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select banner(s)'));
        } else {
            try {
                foreach ($IDList as $itemId) {
                    $_model = Mage::getModel('smvendors/banner')
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
        $IDList = $this->getRequest()->getParam('banner');
        if(!is_array($IDList)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select banner(s)'));
        } else {
            try {
                foreach ($IDList as $itemId) {
                    $_model = Mage::getSingleton('smvendors/banner')
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
            $uploader = new My_Ibanner_Media_Uploader('image');
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save(
                    Mage::getSingleton('smvendors/config')->getBaseMediaPath()
            );

            $result['url'] = Mage::getSingleton('smvendors/config')->getMediaUrl($result['file']);
            $result['cookie'] = array(
                    'name'     => session_name(),
                    'value'    => $this->_getSession()->getSessionId(),
                    'lifetime' => $this->_getSession()->getCookieLifetime(),
                    'path'     => $this->_getSession()->getCookiePath(),
                    'domain'   => $this->_getSession()->getCookieDomain()
            );
        } catch (Exception $e) {
            $result = array('error'=>$e->getMessage(), 'errorcode'=>$e->getCode());
        }

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function categoriesJsonAction()
    {
        $bannerId     = $this->getRequest()->getParam('id');
        $_model  = Mage::getModel('smvendors/banner')->load($bannerId);
        Mage::register('banner_data', $_model);
        Mage::register('current_banner', $_model);

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('smvendors/adminhtml_banner_edit_tab_category')
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
    protected function _title($text = null, $resetIfExists = true)
    {
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