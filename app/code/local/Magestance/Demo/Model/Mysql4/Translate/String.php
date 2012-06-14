<?php
class Magestance_Demo_Model_Mysql4_Translate_String extends Mage_Core_Model_Mysql4_Abstract
{
	
	/**
	 * Define main table
	 *
	 */
    public function _construct()
    {    
        $this->_init('demo/translation', 'translation_id');
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
    		$select = $this->_getReadAdapter()->select()
    		->from($this->getMainTable())
    		->where($this->getMainTable().'.string=:tr_string');
    		
    		$result = Mage::getModel('demo/translation')->getEntryByString($value);

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
    	
    	$string_id = Mage::getModel('demo/string')->getIdByString($object->getString());
    	$translation_items = Mage::getModel('demo/translation')
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
     * Delete translates
     *
     * @param string $string
     * @param string $locale
     * @param int|null $storeId
     * @return Mage_Core_Model_Resource_Translate_String
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
    	
    	$string_id = Mage::getModel('demo/string')->getIdByString($string);
    	
    	Mage::getModel('demo/translation')->deleteTranslation($string_id, $locale, $storeId);

    	return $this;
    }
    
    /**
     * Save translation
     *
     * @param String $string
     * @param String $translate
     * @param String $locale
     * @param int|null $storeId
     * @return Mage_Core_Model_Resource_Translate_String
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

    	Mage::getModel('demo/translate')->addEntry($item);
    
    	return $this;
    }
}