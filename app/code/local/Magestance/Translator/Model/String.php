<?php

class Magestance_Translator_Model_String extends Mage_Core_Model_Abstract
{	
	public function _construct()
	{
		parent::_construct();
		$this->_init('translator/string');
	}
	
	public function createItem($item)
	{
		$string_id = $this->getResource()->getIdByParams($item);
		if (!$string_id) {
			$data = array();
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
			$this->updateItem($item);
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
			$data['string'] = $item['string'];
		}
		
		$string_id = $this->getResource()->getIdByParams($item);
		if ($string_id && $string_id != $item['string_id']) {
			$this->load($item['string_id'])->delete();
			$data['string_id'] = $string_id;
			$this->load($string_id)->setData($data)->save();
		} else {
			$data['string_id'] = $item['string_id'];
			$this->load($data['string_id'])->setData($data)->save();
		}
	}
	
	public function getIdByString($string)
	{
		$item = array('string' => $string);
		return $this->getResource()->getIdByParams($item);
	}
	
	public function getItemByString($string)
	{
		$id = $this->getIdByString($string);
		return $this->load($id);
	}
}