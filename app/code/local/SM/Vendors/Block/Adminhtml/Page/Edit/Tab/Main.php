<?php

class SM_Vendors_Block_Adminhtml_Page_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    protected function _prepareForm() {
        /* @var $model Mage_Cms_Model_Page */
        $model = Mage::registry('page_data');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }


        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('smvendors')->__('Page Information')));

        if ($model && $model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'id',
            ));
        }
        if ($vendor = Mage::helper('smvendors')->getVendorLogin()) {
            $fieldset->addField('vendor_id', 'hidden', array(
                'name' => 'vendor_id',
                'value' => $vendor->getVendorId()
            ));
            $vendorId = $vendor->getId();
        } else {
            $fieldset->addField('vendor_id', 'select', array(
                'name' => 'vendor_id',
                'label' => Mage::helper('smvendors')->__('Vendor'),
                'required' => true,
                'values' => Mage::getResourceModel('smvendors/vendor_collection')->toOptionArray(),
                'disabled' => $isElementDisabled
            ));
        }

        $fieldset->addField('title', 'text', array(
            'name' => 'title',
            'label' => Mage::helper('smvendors')->__('Page Title'),
            'title' => Mage::helper('smvendors')->__('Page Title'),
            'required' => true,
            'disabled' => $isElementDisabled
        ));

        $fieldset->addField('identifier', 'text', array(
            'name' => 'identifier',
            'label' => Mage::helper('smvendors')->__('URL Key'),
            'title' => Mage::helper('smvendors')->__('URL Key'),
            'required' => true,
            'class' => 'validate-identifier',
            'note' => Mage::helper('smvendors')->__('Relative to Website Base URL'),
            'disabled' => $isElementDisabled
        ));
        if (!Mage::helper('smvendors')->getVendorLogin()) {
            $fieldset->addField('applyforall', 'checkbox', array(
                'label' => Mage::helper('web')->__('Apply page title changing to all other vendor'),
                'name' => 'applyforall',
                'value' => '1',
                'note' => Mage::helper('smvendors')->__('Apply to all other vendor\'s page which has same url-key'),
            ));
        }

        if (!Mage::helper('smvendors')->getVendorLogin()) {
            $fieldset->addField('applycontentforall', 'checkbox', array(
                'label' => Mage::helper('web')->__('Apply page content changing to all other vendor'),
                'name' => 'applycontentforall',
                'value' => '1',
                'note' => Mage::helper('smvendors')->__('Apply to all other vendor\'s page which has same url-key'),
            ));
        }

        /*
          $fieldset->addField('root_template', 'select', array(
          'name'     => 'root_template',
          'label'    => Mage::helper('smvendors')->__('Layout'),
          'required' => true,
          'values'   => Mage::getSingleton('page/source_layout')->toOptionArray(),
          'disabled' => $isElementDisabled
          ));
          if (!$model->getId()) {
          $model->setRootTemplate(Mage::getSingleton('page/source_layout')->getDefaultValue());
          }

          $fieldset->addField('layout_update_xml', 'textarea', array(
          'name'      => 'layout_update_xml',
          'label'     => Mage::helper('cms')->__('Layout Update XML'),
          'style'     => 'height:24em;',
          'disabled'  => $isElementDisabled
          ));

          $fieldset->addField('meta_title', 'textarea', array(
          'name'      => 'meta_title',
          'label'     => Mage::helper('smvendors')->__('Meta title'),
          'title'     => Mage::helper('smvendors')->__('Meta title'),
          'required'  => false,
          'disabled'  => $isElementDisabled
          ));

          $fieldset->addField('meta_desrciption', 'textarea', array(
          'name'      => 'meta_desrciption',
          'label'     => Mage::helper('smvendors')->__('Meta Desciption'),
          'title'     => Mage::helper('smvendors')->__('Meta Desciption'),
          'required'  => false,
          'disabled'  => $isElementDisabled
          ));

          $fieldset->addField('meta_keywords', 'textarea', array(
          'name'      => 'meta_keywords',
          'label'     => Mage::helper('smvendors')->__('Meta keywords'),
          'title'     => Mage::helper('smvendors')->__('Meta keywords'),
          'required'  => false,
          'disabled'  => $isElementDisabled
          ));
         */

        /*
          $fieldset->addField('active', 'select', array(
          'label'     => Mage::helper('smvendors')->__('Status'),
          'title'     => Mage::helper('smvendors')->__('Page Status'),
          'name'      => 'active',
          'required'  => true,
          'options'   => array(
          1 => 'Enable',
          0 => 'Disable'
          ),
          'disabled'  => $isElementDisabled,
          ));


          if (!$model->getId()) {
          $model->setData('active', $isElementDisabled ? '0' : '1');
          if(!empty($vendorId)){
          $model->setData('vendor_id',$vendorId);
          }
          }
         */



        if ($model)
            $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel() {
        return Mage::helper('smvendors')->__('Page Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle() {
        return Mage::helper('smvendors')->__('Page Information');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab() {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden() {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action) {
        return true;
        return Mage::getSingleton('admin/session')->isAllowed('cms/page/' . $action);
    }

}
