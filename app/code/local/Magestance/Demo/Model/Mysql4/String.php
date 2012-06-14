<?php
class Magestance_Demo_Model_Mysql4_String extends Mage_Core_Model_Mysql4_Abstract
{
    
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
				->addFieldToFilter('store_id',array('in'=>array(0,$storeId)))
				->addFieldToFilter('locale',array('eq'=>$locale))
				->setOrder('store_id');
		
		$results = array();
		foreach ($collection as $item)
		{
			$string = Mage::getModel('demo/string')->load($item['string_id'])->getString();
			Mage::log($string);
			$results[unserialize($string)] = unserialize($item['translation']);
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