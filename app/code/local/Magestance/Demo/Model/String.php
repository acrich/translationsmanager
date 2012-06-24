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
		$string_id = $this->getIdByParams($item);
		if (!$string_id) {
			$this->setString(serialize($item['string']));
			preg_match_all("/%(?:[0-9]+\\\$)?[\+\-]?(?:[ 0]|\'.)?-?[0-9]*(?:\.[0-9]+)?[bcdeEufFgGosxX]/", $item['string'], $results);
			$parameters = array();
			for ($i = 0; $i < count($results[0]); $i++) {
				$parameters[] = array('hardcoded' => true, 'position' => $i, 'orig_position' => $i, 'value' => '');
			}
			$this->setParameters(serialize($parameters));
			$this->setStatus(true);
			if (array_key_exists('module', $item) && !is_null($item['module'])) {
				$this->setModule($item['module']);
			}
			$this->save();
			$string_id = $this->getStringId();
		} else {
			$item['string_id'] = $string_id;
			$this->updateItem($item);
		}
		return $string_id;
	}
	
	//@todo add module and status to the update as well.
	public function updateItem($item)
	{
		if (array_key_exists('param', $item) && count($item['param'])) {
			$this->load($item['string_id']);
			$params = unserialize($this->getParameters());
			if (!is_array($params)) {
				$params = array();
			}
			foreach ($item['param'] as $key => $param) {
				if (!array_key_exists($key, $params)) {
					$params[$key] = array();
				}
				$params[$key]['hardcoded'] = $param['hardcoded'] == 'on' ? true : false;
				$params[$key]['position'] = $param['position'];
				$params[$key]['value'] = $param['param'];
			}
			$this->setParameters(serialize($params))->save();
		}
	}
	
	public function getIdByParams($item)
	{
		$col = $this->getCollection();
		$col->getSelect()->where('string = ?', serialize($item['string']));
		if (array_key_exists('module', $item)) {
			$col->getSelect()->where('module = ?', $item['module']);
		}
		$items = $col->load();
		$id = count($items) ? $items->getFirstItem()->getStringId() : false;
	
		return $id;
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
		$id = $this->getIdByString($string);
		return $this->load($id);
	}
}