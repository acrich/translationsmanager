<?php
class Wheelbarrow_Translator_Model_Mysql4_String extends Mage_Core_Model_Mysql4_Abstract
{
	const SCOPE_SEPARATOR = '::';
    
	public function _construct()
    {    
        $this->_init('translator/string', 'string_id');
    }
	
	/**
	 * Retrieve translation array for store / locale code
	 *
	 * @param int $storeId
	 * @param string|Zend_Locale $locale
	 * @return array
	 */
	public function getTranslationArray($storeId = null, $locale = null)
	{		
		if (!Mage::isInstalled()) {
			return array();
		}
		
		if (is_null($storeId)) {
			$storeId = Mage::app()->getStore()->getId();
		}
		
		$collection = Mage::getModel('translator/translation')
				->getCollection()
				->addFieldToFilter('store_id',array('in'=>array(Mage_Core_Model_App::ADMIN_STORE_ID,$storeId)));
		if (!is_null($locale)) {
			$collection->addFieldToFilter('locale',array('eq'=>$locale));
		}
		$collection->setOrder('store_id')->load();

		return $this->_preparePairs($collection);
	}
	
	/**
	 * Retrieve translations array by strings
	 *
	 * @param array $strings
	 * @param int_type $storeId
	 * @return array
	 */
	public function getTranslationArrayByStrings(array $strings, $storeId = null)
	{
		if (!Mage::isInstalled()) {
			return array();
		}
	
		if (is_null($storeId)) {
			$storeId = Mage::app()->getStore()->getId();
		}

		if (empty($strings)) {
			return array();
		}

		$string_ids = array();
		foreach ($strings as $string) {
			$string_ids[] = Mage::getModel('translator/string')->getIdByString($string);
		}
		
		$collection = Mage::getModel('translator/translation')
				->getCollection()
				->addFieldToFilter('store_id', $storeId)
				->addFieldToFilter('string_id',array('in'=>array($string_ids)))
				->load();
		
		return $this->_preparePairs($collection);
	}
	
	public function getTranslationArrayByModule($locale, $area)
	{
		if (!Mage::isInstalled()) {
			return array();
		}
		$storeId = Mage::app()->getStore()->getId();
		
		$collection = Mage::getModel('translator/translation')
			->getCollection()
			->addFieldToFilter('store_id',array('in'=>array(Mage_Core_Model_App::ADMIN_STORE_ID,$storeId)));
		
		if (!is_null($locale)) {
			$collection->addFieldToFilter('locale',$locale);
		}
		
		$collection->setOrder('store_id')->load();
	
		$results = array();
		foreach ($collection as $item)
		{
			if ($item->getData($area)) {
				$string_item = Mage::getModel('translator/string')->load($item['string_id']);
				if ($string_item->getStatus() != Mage::getModel('translator/status')->getDisabledCode()) {
					$module = $string_item->getModule();
					if (is_null($module) || $module == '') {
						$scope = $storeId;
						$string = $string_item->getString();
					} else {
						$scope = $module;
						$string = $module . Mage_Core_Model_Translate::SCOPE_SEPARATOR . $string_item->getString();
					}
					if (!array_key_exists($scope, $results)) {
						$results[$scope] = array();
					}
					$results[$scope][$string] = $item['translation'];
				}
			}
		}
		
		return $results;
	}
	
	protected function _preparePairs($collection)
	{
		$results = array();
		foreach ($collection as $item)
		{
			$string_item = Mage::getModel('translator/string')->load($item['string_id']);
			if ($string_item->getStatus() != Mage::getModel('translator/status')->getDisabledCode()) {
				$string = $string_item->getString();
				$module = $string_item->getModule();
				if (!is_null($module) && $module != '') {
					$string = $module . self::SCOPE_SEPARATOR . $string;
				}
				$results[$string] = $item['translation'];
			}
		}
		return $results;
	}
	
	public function getMainChecksum()
	{
		return $this->getChecksum($this->getMainTable());
	}
	
	public function getIdByParams($item)
	{
		if (strpos($item['string'], '::') !== false) {
			list($item['module'], $item['string']) = explode('::', $item['string']);
		}
		
		$adapter = $this->_getReadAdapter();
		$string = $adapter->quote($item['string']);
	
		$select = $adapter->select()
			->from($this->getMainTable(), array('string_id'))
			->where('string = ?', $string);
		if (isset($item['module'])) {
			$select->where('module = ?', $item['module']);
		} elseif (array_key_exists('module', $item)) {
			$select->where('module IS NULL');
		}
		
		return $adapter->fetchOne($select);
	}
	
	protected function _beforeSave(Mage_Core_Model_Abstract $object)
	{
		$string = $object->getData('string');
		$string = $this->_getWriteAdapter()->quote($string);
		$object->setData('string', $string);
		
		return $this;
	}
	
	protected function _afterLoad(Mage_Core_Model_Abstract $object)
	{
		$string = $object->getData('string');
		$string = preg_replace( "/^\'(.*)\'$/U", "$1", $string);
		$string = stripslashes($string);
		$object->setData('string', $string);
		
		return $this;
	}
}