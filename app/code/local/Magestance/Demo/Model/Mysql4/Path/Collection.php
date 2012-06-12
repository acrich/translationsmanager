<?php

class Magestance_Demo_Model_Mysql4_Path_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct()
	{
		$this->_init('demo/path');
	}
}