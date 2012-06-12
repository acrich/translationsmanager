<?php

class Magestance_Demo_Model_String extends Mage_Core_Model_Abstract
{	
	public function _construct()
	{
		parent::_construct();
		$this->_init('demo/string');
	}
	
	public function createItem($string, $module = null)
	{
		$string_id = $this->getIdByString($string);
		if (!$string_id) {
			$this->setString($string);
			if ($module) {
				$this->setModule($module);
			}
			$this->save();
			$string_id = $this->getStringId();
		}
		return $string_id;
	}
	
	public function getIdByString($string)
	{
		$items = $this->getCollection()->addFieldToFilter('string', $string)->load();
		
		$id = count($items) ? $items->getFirstItem()->getStringId() : false;
		
		return $id;
	}
}