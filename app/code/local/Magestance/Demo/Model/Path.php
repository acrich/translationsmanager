<?php

class Magestance_Demo_Model_Path extends Mage_Core_Model_Abstract
{	
	public function _construct()
	{
		parent::_construct();
		$this->_init('demo/path');
	}
	
	public function createItem($path, $file, $offset, $string_id)
	{
		$path_id = $this->getMatchingId($path, $file, $offset, $string_id);
		
		if (!$path_id) {
			$this->setPath($path);
			$this->setFile($file);
			$this->setOffset($offset);
			$this->setStringId($string_id);
			$this->save();
			$path_id = $this->getPathId();
		}
		
		return $path_id;
	}
	
	public function getMatchingId($path, $file, $offset, $string_id)
	{
		$items = $this->getCollection()
				->addFieldToFilter('path', $path)
				->addFieldToFilter('file', $file)
				->addFieldToFilter('offset', $offset)
				->addFieldToFilter('string_id', $string_id)
				->load();
		
		$id = count($items) ? $items->getFirstItem()->getPathId() : false;
		
		return $id;
	}
}