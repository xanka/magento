<?php
class Company_Web_Block_Answer extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getAnswer()
     { 
        if (!$this->hasData('answer')) {
            $this->setData('answer', Mage::registry('answer'));
        }
        return $this->getData('answer');
        
    }
}