<?php

class Magestance_Demo_Model_Path extends Mage_Core_Model_Abstract
{	
	public function _construct()
	{
		parent::_construct();
		$this->_init('demo/path');
	}
	
	public function createItem($item)
	{
		if (array_key_exists('file', $item) && array_key_exists('offset', $item)) {
			$items = $this->getCollection()
				->addFieldToFilter('path', $item['path'])
				->addFieldToFilter('string_id', $item['string_id'])
				->addFieldToFilter('file', $item['file'])
				->addFieldToFilter('offset', $item['offset'])
				->load();
			if (count($items)) {
				$path_id = $items->getFirstItem()->getPathId();
			} else {
				$items = $this->getCollection()
				->addFieldToFilter('path', $item['path'])
				->addFieldToFilter('string_id', $item['string_id'])
				->load();
				if (count($items)) {
					$match = false;
					foreach ($items as $record) {
						if (is_null($record->getFile()) && is_null($record->getOffset())) {
							$record->setData('file', $item['file'])
							->setData('offset', $item['offset'])
								->save();
							$path_id = $this->getPathId();
							$match = true;
							continue;
						}
					}
					if (!$match) {
						$this->setPath($item['path'])
							->setStringId($item['string_id'])
							->setData('file', $item['file'])
							->setData('offset', $item['offset'])
							->save();
						$path_id = $this->getPathId();
					}
				} else {
					$this->setPath($item['path'])
						->setStringId($item['string_id'])
						->setData('file', $item['file'])
						->setData('offset', $item['offset'])
						->save();
					$path_id = $this->getPathId();
				}
			}
		} else {
			$items = $this->getCollection()
				->addFieldToFilter('path', $item['path'])
				->addFieldToFilter('string_id', $item['string_id'])
				->load();
			if (count($items)) {
				$path_id = $items->getFirstItem()->getPathId();
			} else {
				$this->setPath($item['path'])
				->setStringId($item['string_id'])
				->save();
				$path_id = $this->getPathId();
			}
		}
		return $path_id;
	}
	
	public function getMatchingId($item)
	{
		$items = $this->getCollection()
				->addFieldToFilter('path', $item['path'])
				->addFieldToFilter('string_id', $item['string_id'])
				->load();
		
		$id = count($items) ? $items->getFirstItem()->getPathId() : false;
		
		return $id;
	}
	
	public function getStringIdsByPath($path) {
		$items = $this->getCollection()
		->addFieldToFilter('path', array('like' => '%' . $path . '%'))
		->load();
		
		$string_ids = array();
		foreach ($items as $item) {
			$id = $item->getStringId();
			$string_ids[$id] = $id;
		}
		return $string_ids;
	}
}