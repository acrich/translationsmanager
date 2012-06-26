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
				->addFieldToFilter('store_id',array('in'=>array(Mage_Core_Model_App::ADMIN_STORE_ID,$storeId)));
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

		$string_ids = array();
		foreach ($strings as $string) {
			$string_ids[] = Mage::getModel('demo/string')->getIdByString($string);
		}
		
		$collection = Mage::getModel('demo/translation')
				->getCollection()
				->addFieldToFilter('store_id',array('eq'=>$storeId))
				->addFieldToFilter('string_id',array('in'=>array($string_ids)));

		$results = array();
		foreach ($collection as $item)
		{
			$string_item = Mage::getModel('demo/string')->load($item['string_id']);
			$string = unserialize($string_item->getString());
			if (!is_null($string_item->getModule())) {
				$string = $string_item->getModule() . '::' . $string;
			}
			$results[$string] = unserialize($item['translation']);
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