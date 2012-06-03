<?php

class Magestance_Demo_Model_Translate extends Mage_Core_Model_Translate
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('demo/translator');
    }
    
    
    /**
     * Retrieve DB resource model
     *
     * @return unknown
     */
    public function getResource()
    {
    	return Mage::getResourceSingleton('demo/translator');
    }
}