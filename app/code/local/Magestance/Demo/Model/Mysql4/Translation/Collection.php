<?php

class Magestance_Demo_Model_Mysql4_Translation_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct()
	{
		$this->_init('demo/translation');
	}
}