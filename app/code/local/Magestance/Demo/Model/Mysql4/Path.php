<?php
class Magestance_Demo_Model_Mysql4_Path extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
    {    
    	$this->_isPkAutoIncrement = false;
        $this->_init('demo/path', 'path_id');
    }
}