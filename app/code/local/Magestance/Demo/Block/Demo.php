<?php
class Magestance_Demo_Block_Demo extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
    	$this->getLayout()->getBlock('head')->setTitle('shay was here too');
		return parent::_prepareLayout();
    }
    
     public function getDemo()     
     { 
        if (!$this->hasData('demo')) {
            $this->setData('demo', Mage::registry('demo'));
        }
        return $this->getData('demo');
        
    }
}