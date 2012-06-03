<?php

class Magestance_Demo_Model_Entity_Translator_Collection extends Mage_Eav_Model_Entity_Collection_Abstract
{
	public function _construct()
	{
		$this->_init('demo/translator');
	}
	
	
	/**
	 * Retrieve collection items
	 *
	 * @return array
	 */
	public function getItems()
	{
		return $this->_items;
	}

	
	/**
	 * Get collection size
	 *
	 * @return int
	 */
	public function getSize()
	{
		$this->_totalRecords = count($this->getItems());
		return intval($this->_totalRecords);
	}
}