<?php

class SM_Dropship_Block_Adminhtml_Shipping_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm() {
		$form = new Varien_Data_Form();
		$htmlIdPrefix = $form->getHtmlIdPrefix();
		$fieldset = $form->addFieldset('multiflatrate_config_form', array('legend' => Mage::helper('smdropship')->__('Shipping Config')));
		$model = Mage::registry('current_rate');
		
		if ($model->getId()) {
			$fieldset->addField('rate_id', 'hidden', array(
					'name' => 'rate_id',
			));
		}
		
		$fieldset->addField('title', 'text', array(
				'label' => Mage::helper('smdropship')->__('Title'),
				'class' => 'required-entry',
				'required' => true,
				'name' => 'title'
		));
		
		$fieldset->addField('description', 'textarea', array(
				'label' => Mage::helper('smdropship')->__('Description'),
				'name' => 'description'
		));
		
		$fieldset->addField('in_related_vendors', 'hidden', array(
				'label' => Mage::helper('smdropship')->__('Vendors'),
				'name' => 'vendor_ids'
		));
		
		$fieldset->addField('price', 'text', array(
				'label' => Mage::helper('smdropship')->__('Price'),
				'class' => 'required-entry',
				'required' => true,
				'name' => 'price'
		));
		
		$fieldset->addField('type', 'select', array(
				'label' => Mage::helper('smdropship')->__('Type'),
				'class' => 'required-entry',
				'required' => true,
				'name' => 'type',
				'values' => array(
						array(
								'label' => Mage::helper('smdropship')->__('Not Free'),
								'value' => 'nofree'
						),
						array(
								'label' => Mage::helper('smdropship')->__('Free'),
								'value' => 'free'
						)
				)
		),'to');
		
		$fieldset->addField('order_amount_limit', 'text', array(
				'label' => Mage::helper('smdropship')->__('Order Amount Limit'),
				'display'   => 'none',
				'name' => 'order_amount_limit'
		),'type');
		
		// define field dependencies
		//if ($this->getFieldVisibility('type') && $this->getFieldVisibility('order_amount_limit')) {
			
			$this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
					->addFieldMap("{$htmlIdPrefix}type", 'type')
					->addFieldMap("{$htmlIdPrefix}order_amount_limit", 'order_amount_limit')
					->addFieldDependence('order_amount_limit', 'type', 'free')
			);
		//}
		

		$fieldset->addField('active', 'select', array(
				'label' => Mage::helper('smdropship')->__('Status'),
				'class' => 'required-entry',
				'required' => true,
				'name' => 'active',
				'values' => array(
					array(
						'label' => Mage::helper('smdropship')->__('Disable'),
						'value' => 0
					),
					array(
						'label' => Mage::helper('smdropship')->__('Enable'),
						'value' => 1
					)
				)
		));
		$form->setValues($model->getData());
		$this->setForm($form);
		return parent::_prepareForm();
	}
}
