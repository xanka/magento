<?php

class SM_Vendors_Block_Adminhtml_Banner_Edit_Tab_Main
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareForm()
    {
        /* @var $model Mage_Cms_Model_Banner */
        $model = Mage::registry('banner_data');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }


        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('banner_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('smvendors')->__('Banner Information')));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'id',
            ));
        }
		
		if($vendor = Mage::helper('smvendors')->getVendorLogin()){
            $fieldset->addField('vendor_id', 'hidden', array(
                'name' => 'vendor_id'
            ));
			$vendorId = $vendor->getId();
        }
		else{
			$fieldset->addField('vendor_id', 'select', array(
				'name'     => 'vendor_id',
				'label'    => Mage::helper('smvendors')->__('Vendor'),
				'required' => true,
				'values'   => Mage::getResourceModel('smvendors/vendor_collection')->toOptionArray(),
				'disabled' => $isElementDisabled
			));
		}

        $fieldset->addField('title', 'text', array(
            'name'      => 'title',
            'label'     => Mage::helper('smvendors')->__('Banner Title'),
            'title'     => Mage::helper('smvendors')->__('Banner Title'),
            'required'  => true,
            'disabled'  => $isElementDisabled
        ));
		
        $fieldset->addField('width', 'text', array(
            'name'      => 'width',
            'label'     => Mage::helper('smvendors')->__('Banner Width'),
            'title'     => Mage::helper('smvendors')->__('Banner Width'),
            'required'  => true,
            'disabled'  => $isElementDisabled
        ));
        
        $fieldset->addField('height', 'text', array(
            'name'      => 'height',
            'label'     => Mage::helper('smvendors')->__('Banner Height'),
            'title'     => Mage::helper('smvendors')->__('Banner Height'),
            'required'  => true,
            'disabled'  => $isElementDisabled
        ));
        
        $fieldset->addField('position', 'multiselect', array(
			'name'     => 'position',
			'label'    => Mage::helper('smvendors')->__('Position'),
			'required' => true,
			'values'   => Mage::getModel('smvendors/banner_position')->toOptionArray(),
			'disabled' => $isElementDisabled
		));
        
		
		$bigImage = '';
		if($model->getImage() !=''){
		
			$bigImage = '<img style="width:300px" src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$model->getImage().'"/>';
		}
		
		$fieldset->addField('image', 'image', array(
			'label' => Mage::helper('smvendors')->__('Image'),
			'name' => 'image',
			'after_element_html' => $bigImage
		));
		
		
		/*
		try{
            $config = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
        }
        catch (Exception $ex){
            $config = null;
        }
		$fieldset->addField('image', 'editor', array(
			'name'      => 'image',
			'label'     => Mage::helper('smvendors')->__('Banner'),
			'title'     => Mage::helper('smvendors')->__('Banner'),
			'style'     => 'width:700px; height:500px;',
			'wysiwyg'   => 'true',
            'config'    => $config	
		));
		*/

        $fieldset->addField('active', 'select', array(
            'label'     => Mage::helper('smvendors')->__('Status'),
            'title'     => Mage::helper('smvendors')->__('Banner Status'),
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
		
        

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('smvendors')->__('Banner Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('smvendors')->__('Banner Information');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        return true;
		return Mage::getSingleton('admin/session')->isAllowed('cms/banner/' . $action);
    }
}
