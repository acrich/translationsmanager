<?php
class Magestance_Demo_Model_Mysql4_String extends Mage_Core_Model_Mysql4_Abstract
{
	const SCOPE_SEPARATOR = '::';
    
	public function _construct()
    {    
        $this->_init('demo/string', 'string_id');
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
		
		$collection = Mage::getModel('demo/translation')
				->getCollection()
				->addFieldToFilter('store_id',array('in'=>array(0,$storeId)));
		if (!is_null($locale)) {
			$collection->addFieldToFilter('locale',array('eq'=>$locale));
		}
		$collection->setOrder('store_id')->load();
		
		$results = array();
		foreach ($collection as $item)
		{
			$string_item = Mage::getModel('demo/string')->load($item['string_id']);
			$string = unserialize($string_item->getString());
			$module = $string_item->getModule();
			if (!is_null($module) && $module != '') {
				$string = $module . self::SCOPE_SEPARATOR . $string;
			}
			$results[$string] = unserialize($item['translation']);
		}
		
		return $results;
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
		//@todo get all the string_ids through the strings array and use them in the query.
		$collection = Mage::getModel('demo/translation')
				->getCollection()
				->addFieldToFilter('store_id',array('eq'=>$storeId))
				->addFieldToFilter('string_id',array('in'=>array($strings)));

		$results = array();
		foreach ($collection as $item)
		{
			$string = Mage::getModel('demo/string')->load($item['string_id'])->getString();
			$results[unserialize($string)] = unserialize($item['translation']);
		}
		
		return $results;
	}
	
	/**
	 * Retrieve table checksum
	 *
	 * @return int
	 */
	public function getMainChecksum()
	{
		return $this->getChecksum($this->getEntityTable());
	}
}