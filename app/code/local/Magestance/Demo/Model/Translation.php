<?php

class Magestance_Demo_Model_Translation extends Mage_Core_Model_Abstract
{

	public function _construct()
	{
		parent::_construct();
		$this->_init('demo/translation');
	}
	
	public function createItem($item)
	{
		if (!$this->getIdByStringId($item['string_id'])) {
			$this->setTranslation(serialize($item['translation']));
			$this->setStringId($item['string_id']);
			if (array_key_exists('locale', $item)) {
				$this->setLocale($item['locale']);
			}
			if (array_key_exists('store_id', $item)) {
				$this->setStoreId($item['store_id']);
			} else {
				$this->setStoreId(0);
			}
			$this->save();
			return $this->getTranslationId();
		}
	}
	
	public function getIdByStringId($string_id)
	{
		//@todo condition this with store ids too.
		$items = $this->getCollection()->addFieldToFilter('string_id', $string_id)->load();
		
		$id = count($items) ? $items->getFirstItem()->getTranslationId() : false;
		
		return $id;
	}
}