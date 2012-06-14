<?php

class Magestance_Demo_Model_String extends Mage_Core_Model_Abstract
{	
	public function _construct()
	{
		parent::_construct();
		$this->_init('demo/string');
	}
	
	public function createItem($item)
	{
		$string_id = $this->getIdByString($item['string']);
		if (!$string_id) {
			$this->setString(serialize($item['string']));
			if (!is_null($item['module'])) {
				$this->setModule($item['module']);
			}
			$this->save();
			$string_id = $this->getStringId();
		}
		return $string_id;
	}
	
	public function getIdByString($string)
	{
		$col = $this->getCollection();
		$col->getSelect()->where('string = ?', serialize($string));
		$items = $col->load();
		$id = count($items) ? $items->getFirstItem()->getStringId() : false;
		
		return $id;
	}
	
	public function getItemByString($string)
	{
		$id = getIdByString($string);
		return $this->load($id);
	}
}