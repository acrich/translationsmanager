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
			
			$this->setData($data)->save();
			$string_id = $this->getStringId();
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
		if (isset($item['module'])) {
			$data['module'] = $item['module'];
		}
		if (isset($item['string']) && $item['string'] != '') {
			if (strpos($item['string'], '::') !== false) {
				list($item['module'], $item['string']) = explode('::', $item['string']);
			}
			$data['string'] = $item['string'];
		}
		
		$string_id = $this->getResource()->getIdByParams($item);
		if ($string_id && $string_id != $item['string_id']) {
			$this->load($item['string_id'])->delete();
			$data['string_id'] = $string_id;
			//@todo get this out of the if statement:
			$this->load($data['string_id'])->setData($data)->save();
		} else {
			$data['string_id'] = $item['string_id'];
			$this->load($data['string_id'])->setData($data)->save();
		}
		return $data['string_id'];
	}
	
	//@todo change this function's name, if it should even exist.
	public function prepareForSave($collection, $item) {
		
		if (!array_key_exists('module', $item)) {
			$item['module'] = null;
		}
			
		if (strpos($item['string'], '::') !== false) {
			list($item['module'], $item['string']) = explode('::', $item['string']);
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
		
		return $this->setData($data)->save()->getStringId();
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