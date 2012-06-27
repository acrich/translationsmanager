<?php
class Magestance_Translator_Model_Mysql4_Translate_String extends Mage_Core_Model_Mysql4_Abstract
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
    	$main_table = $this->getMainTable();
    	$str_table = $this->_getStringTable();
    	
    	if (is_string($value)) {
    		$select = $this->_getReadAdapter()->select(array('translation', 'store_id', 'locale'))
    		->from($main_table)
    		->joinInner($str_table, $str_table.'.string_id='.main_table.'.string_id', array('string', "group_concat(module) SEPARATOR '::'"))
    		->where($main_table.'.string=:tr_string');
    		$result = $this->_getReadAdapter()->fetchRow($select, array('tr_string'=>serialize($value)));
    		$result['translate'] = unserialize($result['translation']);
    		unset($result['translation']);
    		$object->setData($result);
    		$this->_afterLoad($object);
    		return $result;
    	} else {
    		return parent::load($object, $value, $field);
    	}
    }
    
    /**
     * Retrieve select for load
     *
     * @param String $field
     * @param String $value
     * @param Mage_Core_Model_Abstract $object
     * @return Varien_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
    	$select = parent::_getLoadSelect($field, $value, $object);
    	$select->where('store_id = ?', Mage_Core_Model_App::ADMIN_STORE_ID);
    	return $select;
    }
    
    /**
     * After translation loading
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    public function _afterLoad(Mage_Core_Model_Abstract $object)
    {
    	$string_id = Mage::getModel('translator/string')->getIdByString($object->getString());
    	$translation_items = Mage::getModel('translator/translation')
    		->getCollection()
    		->addFieldToFilter('string_id', $string_id)
    		->load();
    	$translations = array();
    	foreach ($translation_items as $item) {
    		$translations[$item->getStoreId()] = $item->getTranslation();
    	}

    	$object->setStoreTranslations($translations);
    	return parent::_afterLoad($object);
    }
    
    /**
     * Before save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Magestance_Translator_Model_Mysql4_Translate_String
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
    	$string_id = Mage::getModel('translator/string')->getIdByString($object->getString());
    	$translation_id = Mage::getModel('translator/translation')
    		->getCollection()
    		->addFieldToFilter('string_id', $string_id)
    		->addFieldToFilter('store_id', Mage_Core_Model_App::ADMIN_STORE_ID)
    		->getFirstItem()->getTranslationId();

    	$object->setId($translation_id);
    	return parent::_beforeSave($object);
    }
    
    /**
     * Save object object data
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Magestance_Translator_Model_Mysql4_Translate_String
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
     * @return Magestance_Translator_Model_Mysql4_Translate_String
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
     * @return Magestance_Translator_Model_Mysql4_Translate_String
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
     * @return Magestance_Translator_Model_Mysql4_Translate_String
     */
    public function saveTranslate($string, $translate, $locale = null, $storeId = null)
    {    
    	if (is_null($locale)) {
    		$locale = Mage::app()->getLocale()->getLocaleCode();
    	}
    
    	if (is_null($storeId)) {
    		$storeId = Mage::app()->getStore()->getId();
    	}
    	
    	$original = $string;
    	if (strpos($original, '::') !== false) {
    		list($scope, $original) = explode('::', $original);
    	}
    	
    	$item = array(
    				'string' => $original, 
    				'translation' => $translate, 
    				'store_id' => $storeId,
    				'locale' => $locale
    			);
    	
    	if (!is_null($scope)) {
    		$item['module'] = $scope;
    	}

    	Mage::getModel('translator/translate')->addEntry($item);
    
    	return $this;
    }
}