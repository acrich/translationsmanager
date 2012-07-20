<?php

class Wheelbarrow_Translator_Model_Mysql4_Cache_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct()
	{
		$this->_init('translator/cache');
	}
}