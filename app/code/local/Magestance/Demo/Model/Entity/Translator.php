<?php
class Magestance_Demo_Model_Entity_Translator extends Mage_Eav_Model_Entity_Abstract
{
	/**
	 * Define main table
	 *
	 */
	protected function _construct()
	{
		$resource = Mage::getSingleton('core/resource');
		$this->setType('demo_translator');
		$this->setConnection(
				$resource->getConnection('demo_read'),
				$resource->getConnection('demo_write')
		);
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
		
		$collection = Mage::getModel('demo/translator')
				->getCollection()
				->addAttributeToSelect('string')
				->addAttributeToSelect('translate')
				->addAttributeToSelect('locale')
				->addAttributeToSelect('store_id')
				->addFieldToFilter('store_id',array('in'=>array(0,$storeId)))
				->addFieldToFilter('locale',array('eq'=>$locale))
				->setOrder('store_id');
		
		$results = array();
		foreach ($collection as $item)
		{
			$results[$item['string']] = $item['translate'];
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
		
		$collection = Mage::getModel('demo/translator')
				->getCollection()
				->addAttributeToSelect('string')
				->addAttributeToSelect('translate')
				->addAttributeToSelect('store_id')
				->addFieldToFilter('store_id',array('eq'=>$storeId))
				->addFieldToFilter('string',array('in'=>array($strings)));

		$results = array();
		foreach ($collection as $item)
		{
			$results[$item['string']] = $item['translate'];
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