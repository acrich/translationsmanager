<?php

class Magestance_Demo_Model_Cache extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('demo/cache');
    }
    
    public function createItem($name, $register)
    {
    	$this->setName($name)
    		->setRegister($register)
    		->save();
    	
    	return $this->getId();
    }
}