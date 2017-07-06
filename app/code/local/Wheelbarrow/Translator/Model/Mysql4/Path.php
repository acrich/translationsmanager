<?php
class Wheelbarrow_Translator_Model_Mysql4_Path extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
    {    
    	$this->_isPkAutoIncrement = false;
        $this->_init('translator/path', 'path_id');
    }
}