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
		//@todo the check for existing values currently doesn't account for different store ids.
		if ($translation_id = $this->getIdByStringId($item['string_id'])) {
			$this->load($translation_id)
				->setTranslation(serialize($item['translation']))
				->save();
			return $translation_id;
		} else {
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
		//@todo condition this with store ids / locales + add defaults to admin view.
		$items = $this->getCollection()->addFieldToFilter('string_id', $string_id)->load();
		
		$id = count($items) ? $items->getFirstItem()->getTranslationId() : false;
		
		return $id;
	}
	
	public function deleteTranslation($string_id, $locale, $storeId)
	{
		$items = $this->getCollection()
			->addFieldToFilter('string_id', $string_id)
			->addFieldToFilter('store_id', $storeId);
		if (!is_null($locale)) {
			$items->addFieldToFilter('locale', $locale);
		}
		
		$items->load();
		if (count($items)) {
			$items->getFirstItem()->delete();
		}
	}
}