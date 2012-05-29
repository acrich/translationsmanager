<?php
class Magestance_Demo_Model_Resource_Translate extends Mage_Eav_Model_Entity_Abstract
{
	/**
	 * Define main table
	 *
	 */
	protected function _construct()
	{
		$resource = Mage::getSingleton('core/resource');
		$this->setType('demo_translate');
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
	
		$adapter = $this->_getReadAdapter();
		if (!$adapter) {
			return array();
		}
	
		$select = $adapter->select()
		->from($this->getMainTable(), array('string', 'translate'))
		->where('store_id IN (0 , :store_id)')
		->where('locale = :locale')
		->order('store_id');
	
		$bind = array(
				':locale'   => (string)$locale,
				':store_id' => $storeId
		);
	
		return $adapter->fetchPairs($select, $bind);
	
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
	
		$adapter = $this->_getReadAdapter();
		if (!$adapter) {
			return array();
		}
	
		if (empty($strings)) {
			return array();
		}
	
		$bind = array(
				':store_id'   => $storeId
		);
		$select = $adapter->select()
		->from($this->getMainTable(), array('string', 'translate'))
		->where('string IN (?)', $strings)
		->where('store_id = :store_id');
	
		return $adapter->fetchPairs($select, $bind);
	}
	
	/**
	 * Retrieve table checksum
	 *
	 * @return int
	 */
	public function getMainChecksum()
	{
		return $this->getChecksum($this->getMainTable());
	}
}