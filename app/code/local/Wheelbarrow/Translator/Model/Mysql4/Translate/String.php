<?php
class Wheelbarrow_Translator_Model_Mysql4_Translate_String extends Mage_Core_Model_Mysql4_Abstract
{
	
	/**
	 * Define main table
	 *
	 */
    public function _construct()
    {    
        $this->_init('translator/translation', 'translation_id');
    }
    
    protected function _getStringTable()
    {
    	return Mage::getModel('translator/string')->getResource()->getMainTable();
    }
    
    /**
     * Load
     *
     * @param Mage_Core_Model_Abstract $object
     * @param String $value
     * @param String $field
     * @return array
     */
    public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {
    	if (is_string($value)) {
    		$string_id = Mage::getModel('translator/string')->getIdByString($value);
    		$items = Mage::getModel('translator/translation')->getTranslationsByStringId($string_id);
    		$data = array(
    				'string' => $value,
    				'store_translations' => array()
    				);
    		foreach ($items as $item) {
    			if ($item->getStoreId() == 0) {
    				$data['key_id'] = $item->getTranslationId();
    				$data['store_id'] = '0';
    				$data['translate'] = $item->getTranslation();
    				$data['locale'] = $item->getLocale();
    			}
    			$data['store_translations'][$item->getStoreId()] = $item->getTranslation();
    		}
    		$object->setData($data);
    		return $object;
    	}
    }
    
    /**
     * Save object object data
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Wheelbarrow_Translator_Model_Mysql4_Translate_String
     */
    public function save(Mage_Core_Model_Abstract $object)
    {
    	if ($object->isDeleted()) {
    		return $this->deleteTranslate($object->getString(), $object->getLocale(), $object->getStoreId());
    	}
    	$this->_beforeSave($object);

    	Mage::getModel('translator/translate')->addEntry(array(
    				'string' => $object->getString(),
    				'translation' => $object->getTranslate(),
    				'locale' => $object->getLocale(),
    				'store_id' => $object->getStoreId()
    			));

    	$this->_afterSave($object);
    
    	return $this;
    }
    
    /**
     * After save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Wheelbarrow_Translator_Model_Mysql4_Translate_String
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
    	$translations = $object->getStoreTranslations();
    	if (is_array($translations)) {
    		$string_id = Mage::getModel('translator/string')->getIdByString($object->getString());
    		$items = Mage::getModel('translator/translate')->getEntriesByString($object->getString());
    		
    		foreach ($translations as $storeId => $translate) {
    			if (is_null($translate) || $translate=='') {
    				if (array_key_exists($storeId, $items)) {
    					Mage::getModel('translator/translation')->load($items[$storeId])->delete();
    				}
    			} else {
    				if (array_key_exists($storeId, $items)) {
    					Mage::getModel('translator/translation')->updateItem(array(
    								'translation_id' => $items[$storeId],
    								'translation' => $translate
    							));
    				} else {
    					Mage::getModel('translator/translation')->createItem(array(
    								'translation' => $translate,
    								'store_id' => $storeId,
    								'string_id' => $string_id
    							));
    				}
    			}
    		}
    	}
    	return parent::_afterSave($object);
    }
    
    /**
     * Delete translates
     *
     * @param string $string
     * @param string $locale
     * @param int|null $storeId
     * @return Wheelbarrow_Translator_Model_Mysql4_Translate_String
     */
    public function deleteTranslate($string, $locale = null, $storeId = null)
    {
    	if (is_null($locale)) {
    		$locale = Mage::app()->getLocale()->getLocaleCode();
    	}
    	
    	if ($storeId === false) {
    		$storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
    	} elseif ($storeId !== null) {
    		$storeId = $storeId;
    	}
    	
    	$string_id = Mage::getModel('translator/string')->getIdByString($string);
    	
    	Mage::getModel('translator/translation')->deleteTranslation($string_id, $locale, $storeId);

    	return $this;
    }
    
    /**
     * Save translation
     *
     * @param String $string
     * @param String $translate
     * @param String $locale
     * @param int|null $storeId
     * @return Wheelbarrow_Translator_Model_Mysql4_Translate_String
     */
    public function saveTranslate($string, $translate, $locale = null, $storeId = null)
    {    
    	if (is_null($locale)) {
    		$locale = Mage::app()->getLocale()->getLocaleCode();
    	}
    
    	if (is_null($storeId)) {
    		$storeId = Mage::app()->getStore()->getId();
    	}
    	
    	$item = array(
    				'string' => $string, 
    				'translation' => $translate, 
    				'store_id' => $storeId,
    				'locale' => $locale
    			);

    	Mage::getModel('translator/translate')->addEntry($item);
    
    	return $this;
    }
}