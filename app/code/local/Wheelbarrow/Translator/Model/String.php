<?php

class Wheelbarrow_Translator_Model_String extends Mage_Core_Model_Abstract
{	
	public function _construct()
	{
		parent::_construct();
		$this->_init('translator/string');
	}
	
	public function createItem($item)
	{
		//@todo add a check for an empty or non-existing string attribute + a test.
		$string_id = $this->getResource()->getIdByParams($item);
		if (!$string_id) {
			$data = array();
			
			if (strpos($item['string'], '::') !== false) {
				list($item['module'], $item['string']) = explode('::', $item['string']);
			}
			
			if (!isset($item['parameters'])) {
				preg_match_all("/%(?:[0-9]+\\\$)?[\+\-]?(?:[ 0]|\'.)?-?[0-9]*(?:\.[0-9]+)?[bcdeEufFgGosxX]/", $item['string'], $results);
				$item['parameters'] = array();
				for ($i = 0; $i < count($results[0]); $i++) {
					$item['parameters'][] = array('hardcoded' => true, 'code_position' => $i, 'position' => $i, 'value' => '');
				}
			}
			
			$data['parameters'] = serialize($item['parameters']);
			$data['string'] = $item['string'];
			$data['status'] = (isset($item['status'])) ? $item['status'] : 1;
			$data['module'] = (array_key_exists('module', $item)) ? $item['module'] : null;
			
			$model_item = new Wheelbarrow_Translator_Model_String();
			$string_id = $model_item->setData($data)->save()->getStringId();
		} else {
			$item['string_id'] = $string_id;
			$string_id = $this->updateItem($item);
		}
		return $string_id;
	}
	
	public function updateItem($item)
	{		 
		$data = array();
		
		if (isset($item['parameters'])) {
			$data['parameters'] = serialize($item['parameters']);
		}
		if (isset($item['status'])) {
			$data['status'] = $item['status'];
		}
		if (isset($item['string']) && $item['string'] != '') {
			if (strpos($item['string'], '::') !== false) {
				list($item['module'], $item['string']) = explode('::', $item['string']);
			}
			$data['string'] = $item['string'];
		}
		if (isset($item['module'])) {
			$data['module'] = $item['module'];
		}
		//@todo remove the part that directs to createItem once we change everything to setItem().
		//@todo refactor.
		$string_id = $this->getResource()->getIdByParams($item);
		if ($string_id) {
			if (isset($item['string_id']) && $string_id != $item['string_id']) {
				$this->load($item['string_id'])->delete();
			}
			$data['string_id'] = $string_id;
			//@todo get this out of the if statement:
			$this->load($data['string_id'])->setData($data)->save();
		} else {
			if (isset($item['string_id'])) {
				$data['string_id'] = $item['string_id'];
				$this->load($data['string_id'])->setData($data)->save();
			} else {
				$data['string_id'] = $this->createItem($item);
			}
		}
		return $data['string_id'];
	}
	
	//@todo move the match finding to setItem, so that the rest of the logic could be shared with updateItem and 
	// createItem. Then, write specific tests for the collection-based match finding used here.
	public function prepareForSave($collection, $item) {
			
		if (strpos($item['string'], '::') !== false) {
			list($item['module'], $item['string']) = explode('::', $item['string']);
		}
		
		if (!array_key_exists('module', $item)) {
			$item['module'] = null;
		}
		
		$string_id = null;
		foreach ($collection as $row) {
			if (($row['string'] == $item['string']) && ($row['module'] == $item['module'])) {
				$string_id = $row['string_id'];
			}
		}
		
		$data = array();
		if (isset($item['status'])) {
			$data['status'] = $item['status'];
		}
		if (isset($item['module'])) {
			$data['module'] = $item['module'];
		}
		if (isset($item['string']) && $item['string'] != '') {
			$data['string'] = $item['string'];
		}
		
		if (!isset($item['string_id']) && is_null($string_id)) {
			if (!isset($item['parameters'])) {
				preg_match_all("/%(?:[0-9]+\\\$)?[\+\-]?(?:[ 0]|\'.)?-?[0-9]*(?:\.[0-9]+)?[bcdeEufFgGosxX]/", $item['string'], $results);
				$item['parameters'] = array();
				for ($i = 0; $i < count($results[0]); $i++) {
					$item['parameters'][] = array('hardcoded' => true, 'code_position' => $i, 'position' => $i, 'value' => '');
				}
			}
		} else {
			$data['string_id'] = (isset($string_id)) ? $string_id : $item['string_id'];
			if (isset($item['string_id']) && $data['string_id'] != $item['string_id']) {
				$this->load($item['string_id'])->delete();
			}
		}
		
		if (isset($item['parameters'])) {
			$data['parameters'] = serialize($item['parameters']);
		}
		$model_item = new Wheelbarrow_Translator_Model_String();
		$collection->addItem($model_item->setData($data));
		
		return $collection;
	}
	
	public function setItem($item) {
		if (isset($item['string_id']) && $item['string_id'] != 0) {
			$item['string_id'] = $this->updateItem($item);
		} else {
			$item['string_id'] = $this->createItem($item);
		}
		return $item['string_id'];
	}
	
	public function getIdByString($string)
	{
		return $this->getResource()->getIdByParams(array('string' => $string));
	}
	
	public function getItemByString($string)
	{
		return $this->load($this->getIdByString($string));
	}
}