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
		if (array_key_exists('string_id', $item)) {
			if (!array_key_exists('store_id', $item)) {
				if (array_key_exists('locale', $item)) {
					$stores = Mage::app()->getStores();
					$found = false;
					foreach ($stores as $store) {
						if ($store->getConfig('general/locale/code') == $item['locale']) {
							$found = true;
							$item['store_id'] = $store->getId();
							$this->_createItem($item);
						}
					}
					if (!$found) {
						$this->_createDefaultItem($item);
					}
				} else {
					$this->_createDefaultItem($item);
				}
			} else {
				if (!array_key_exists('locale', $item)) {
					$item['locale'] = Mage::app()->getStore($item['store_id'])->getConfig('general/locale/code');
				}
				$this->_createItem($item);
			}
		} else {
			return false;
		}
	}
	
	protected function _createDefaultItem($item)
	{
		$item['store_id'] = 0;
		$item['locale'] = Mage::app()->getStore($item['store_id'])->getConfig('general/locale/code');
		$this->_createItem($item);
	}
	
	protected function _createItem($item)
	{
		if ($translation_id = $this->getIdByParams($item['string_id'], $item['store_id'])) {
			$this->load($translation_id);
			if (array_key_exists('translation', $item) && $item['translation'] != '') {
				$this->setTranslation(serialize($item['translation']))
				->save();
				return $translation_id;
			} else {
				$this->delete();
				return false;
			}
		} else {
			if (array_key_exists('translation', $item) && $item['translation'] != '') {
				$this->setTranslation(serialize($item['translation']));
				$this->setStringId($item['string_id']);
				$this->setStoreId($item['store_id']);
				$this->setLocale($item['locale']);
				$this->save();
				return $this->getTranslationId();
			} else {
				return false;
			}
		}
	}
	
	public function getIdByParams($string_id, $store_id = 0)
	{
		$items = $this->getCollection()
			->addFieldToFilter('string_id', $string_id)
			->addFieldToFilter('store_id', $store_id)
			->load();
	
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
	
	public function getTranslationedStringsByStore($store)
	{
		$items = $this->getCollection()
			->addFieldToFilter('store_id', $store);
		$string_ids = array();
		foreach ($items as $item) {
			$string_ids[] = $item->getStringId();
		}
		return $string_ids;
	}
}