<?php

class Wheelbarrow_Translator_Model_Path extends Mage_Core_Model_Abstract
{	
	public function _construct()
	{
		parent::_construct();
		$this->_init('translator/path');
	}
	
	protected function _preparePathForDb($path) {
		return preg_replace("/http(s?)\:\/\//", '//', $path);
	}
	
	protected function _saveItem($item) {
		$this->setPath($item['path'])
			->setStringId($item['string_id'])
			->setData('file', $item['file'])
			->setData('offset', $item['offset'])
			->save();
		return $this->getPathId();
	}
	
	public function createItem($item)
	{
		$item['path'] = $this->_preparePathForDb($item['path']);
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
						$path_id = $this->_saveItem($item);
					}
				} else {
					$path_id = $this->_saveItem($item);
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
		$item['path'] = $this->_preparePathForDb($item['path']);
		$items = $this->getCollection()
				->addFieldToFilter('path', $item['path'])
				->addFieldToFilter('string_id', $item['string_id'])
				->load();
		
		$id = count($items) ? $items->getFirstItem()->getPathId() : false;
		
		return $id;
	}
	
	public function getStringIdsByPath($path) {
		$path = $this->_preparePathForDb($path);
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
	
	public function getPathIdsByStringId($string_id) {
		$items = $this->getCollection()
			->addFieldToFilter('string_id', $string_id)
			->load();
		
		$path_ids = array();
		foreach ($items as $item) {
			//@todo no reason for unique array keys here. Just remove the first line and modify the second to have no key.
			$id = $item->getPathId();
			$path_ids[$id] = $id;
		}
		return $path_ids;
	}
}