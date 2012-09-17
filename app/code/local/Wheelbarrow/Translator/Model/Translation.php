<?php

class Wheelbarrow_Translator_Model_Translation extends Mage_Core_Model_Abstract
{

	public function _construct()
	{
		parent::_construct();
		$this->_init('translator/translation');
	}
	
	//@todo make all the createItem derivatives return some value.
	
	/**
	 * Creates a new translation item and saves it.
	 * 
	 * @param Array $item
	 */
	public function createItem($item)
	{
		//If there's no string_id attribute, do nothing.
		//Switch the if into a if !, and remove the need for an else clause.
		if (array_key_exists('string_id', $item)) {
			//If there isn't a store_id but there is a locale, create an item for each store with that locale.
			if (!array_key_exists('store_id', $item) || $item['store_id'] == Mage_Core_Model_App::ADMIN_STORE_ID) {
				$default_locale = Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID)->getConfig('general/locale/code');
				if (array_key_exists('locale', $item) && !($item['locale'] == $default_locale)) {
					$stores = Mage::app()->getStores();
					$found = false;
					foreach ($stores as $store) {
						if ($store->getConfig('general/locale/code') == $item['locale']) {
							$found = true;
							$item['store_id'] = $store->getId();
							$this->_createItem($item);
						}
					}
					//If you haven't found any stores with that locale, create an item with defaults.
					if (!$found) {
						$this->_createDefaultItem($item);
					}
				//If there isn't a store_id or a locale, create an item with defaults.
				} else {
					$this->_createDefaultItem($item);
				}
			} else {
				//If there's a store id, but no locale, fill the one based on the other.
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
		$item['store_id'] = Mage_Core_Model_App::ADMIN_STORE_ID;
		$item['locale'] = Mage::app()->getStore($item['store_id'])->getConfig('general/locale/code');
		$this->_createItem($item);
	}
	
	protected function _prepareDataForSave($item) {
		$data = array(
				'translation' => $item['translation'],
				'string_id' => $item['string_id'],
				'store_id' => $item['store_id'],
				'locale' => $item['locale']
		);
		if (isset($item['translation_id']) && $item['translation_id']) {
			$data['translation_id'] = $item['translation_id'];
		}
		//@todo remove primary here and anywhere else it still lingers.
		if (isset($item['primary'])) {
			$data['primary'] = $item['primary'];
		}
		if (isset($item['areas'])) {
			if (array_key_exists('strict', $item) && $item['strict'] == true) {
				foreach (array('frontend', 'adminhtml', 'install') as $area) {
					$data[$area] = in_array($area, $item['areas']);
				}
			}
			//@todo if it's not set to strict then no data[$area] are set, only the general $data['areas'],
			//	in which case remove duplicates won't work right. I bypassed it with an if statement,
			//	but this isn't the right way to do it.
			$this->removeDuplicateAreas($data);
		}
		return $data;
	}
	
	protected function _createItem($item)
	{
		if (array_key_exists('translation', $item) && $item['translation'] != '') {
			//@todo this runs over existing values and prevents updating where appropriate.
			// Change it, and change the unit test accordingly.
			if ($item['translation_id'] = $this->getIdByParams($item)) {
				//@todo this variable isn't used, you probably meant to insert that into $item['translation_id']
				$translation = $this->load($item['translation_id'])->setData($this->_prepareDataForSave($item))->save()->getTranslationId();
			} else {
				if ($this->getIdByParams(array('store_id' => $item['store_id'], 'string_id' => $item['string_id']))) {
					$item['primary'] = false;
				}
				//@todo check whether or not we need to add this part somewhere else too:
				//Setting the areas only when it's not an update:
				$data = $this->_prepareDataForSave($item);
				if (isset($item['areas'])) {
					foreach (array('frontend', 'adminhtml', 'install') as $area) {
						$data[$area] = in_array($area, $item['areas']);
					}
				}

				$item['translation_id'] = $this->setData($data)->save()->getTranslationId();
			}
		} else {
			//@todo add a test for this scenario (existing item, and an update with translation set to nothing).
			if ($item['translation_id'] = $this->getIdByParams($item)) {
				$this->load($item['translation_id'])->delete();
			}
		}
		return $item['translation_id'];
	}
	
	public function removeDuplicateAreas($item) {
		$siblings = $this->getCollection()
			->addFieldToFilter('string_id', $item['string_id'])
			->addFieldToFilter('store_id', $item['store_id']);
		
		if (isset($item['locale'])) {
			$siblings->addFieldToFilter('locale', $item['locale']);
		}

		foreach ($siblings->load() as $sibling) {
			if (isset($item['translation_id']) && $sibling->getTranslationId() == $item['translation_id']) {
				continue;
			}
			$lives = 3;
			foreach (array('frontend', 'adminhtml', 'install') as $area) {
				if (!$sibling->getData($area)) {	
					$lives--;
				} else if (isset($item[$area]) && $item[$area]) {
					$sibling->setData($area, false);
					$lives--;
				}
			}
			if ($lives == 0) {
				$sibling->delete();
			} else {
				$sibling->save();
			}
		}
	}
	
	//@todo either merge this into createItem or merge parts of the latter into this one.
	public function updateItem($item)
	{
		$this->load($item['translation_id']);
		
		if (isset($item['translation']) && $item['translation'] != '') {
			$this->setTranslation($item['translation']);
		}
		
		if (isset($item['areas'])) {
			foreach (array('frontend', 'adminhtml', 'install') as $area) {
				$this->setData($area, in_array($area, $item['areas']));
			}
		}
		
		$data = $this->save()->getData();
		
		if (isset($item['areas'])) {
			$data['areas'] = $item['areas'];
			$this->removeDuplicateAreas($data);
		}
		
	}
	
	public function setItem($item)
	{
		if (isset($item['translation_id']) && $item['translation_id'] != 0) {
			return $this->updateItem($item);
		} else {
			return $this->createItem($item);
		}
	}
	
	public function getIdByParams($item)
	{
		if (is_null($item['store_id'])) {
			$item['store_id'] = Mage_Core_Model_App::ADMIN_STORE_ID;
		}
		
		if (!is_array($item['store_id'])) {
			$item['store_id'] = array($item['store_id']);
		}
		
		$items = $this->getCollection()
			->addFieldToFilter('string_id', $item['string_id'])
			->addFieldToFilter('store_id', array('in'=>$item['store_id']));
		
		if (isset($item['areas'])) {
			foreach (array('frontend', 'adminhtml', 'install') as $area) {
				if (in_array($area, $item['areas'])) {
					$items->addFieldToFilter($area, true);
				}
			}
		}

		return count($items->load()) ? $items->getFirstItem()->getTranslationId() : false;
	}
	
	public function deleteTranslation($item)
	{
		if (!isset($item['translation_id'])) {
			if (isset($item['string_id'])) {
				$items = $this->getCollection()
					->addFieldToFilter('string_id', $item['string_id']);
				if (isset($item['store_id'])) {
					$items->addFieldToFilter('store_id', $item['store_id']);
				}
				if (isset($item['locale'])) {
					$items->addFieldToFilter('locale', $item['locale']);
				}
				if (isset($item['area'])) {
					$items->addFieldToFilter($item['area'], true);
				}
				if (count($items->load()) === 1) {
					$item['translation_id'] = $items->getFirstItem()->getTranslationId();
				}
			}
		}
		return $this->load($item['translation_id'])->delete();
	}
	
	public function getTranslatedStringsByStore($store)
	{
		$items = $this->getCollection()
			->addFieldToFilter('store_id', $store);
		$string_ids = array();
		foreach ($items as $item) {
			$string_ids[] = $item->getStringId();
		}
		return $string_ids;
	}
	
	public function getTranslationsByStringId($string_id)
	{
		return $this->getCollection()
			->addFieldToFilter('string_id', $string_id)
			->load();
	}
	
	public function getStringIdsByArea($area)
	{
		return $this->getCollection()
			->addFieldToFilter($area, true)
			->getColumnValues('string_id');
	}
	
	public function getDuplicatesList($store_id)
	{
		$items = $this->getCollection()
			->addFieldToFilter('store_id', $store_id)
			->load();
		$uniques = array();
		$duplicates = array();
		foreach ($items as $item) {
			$string_id = $item->getStringId();
			if (array_key_exists($string_id, $uniques)) {
				$duplicates[$string_id] = $item->getTranslationId();
			} else {
				$uniques[$string_id] = $item->getTranslationId();
			}
		}
		return implode(',',$duplicates);
	}
}