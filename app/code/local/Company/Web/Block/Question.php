<?php
class Company_Web_Block_Question extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getQuestion()
     { 
        if (!$this->hasData('question')) {
            $this->setData('question', Mage::registry('question'));
        }
        return $this->getData('question');
        
    }
}