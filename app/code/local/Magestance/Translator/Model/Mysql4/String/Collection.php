<?php

class Magestance_Translator_Model_Mysql4_String_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct()
	{
		$this->_init('translator/string');
	}
	
	/**
	 * Add filter by store
	 *
	 * @param int|Mage_Core_Model_Store $store
	 * @param bool $withAdmin
	 * @return Magestance_Translator_Model_Mysql4_String_Collection
	 */
	public function addStoreFilter($store, $withAdmin = false)
	{
		if (!$this->getFlag('store_filter_added')) {
			
			if ($store instanceof Mage_Core_Model_Store) {
				$store = array($store->getId());
			}
	
			if (!is_array($store)) {
				$store = array($store);
			}
	
			if ($withAdmin) {
				$store[] = Mage_Core_Model_App::ADMIN_STORE_ID;
			}
			
			$string_ids = array();
			foreach ($store as $id) {
				$strings = Mage::getModel('translator/translation')->getTranslatedStringsByStore($store);
				foreach ($strings as $string) {
					$string_ids[] = $string;
				}
			}

			$this->addFilter('main_table.string_id', array('in' => $string_ids), 'public');
		}
		return $this;
	}
	
	/**
	 * Perform operations after collection load
	 *
	 * @return Magestance_Translator_Model_Mysql4_String_Collection
	 */
	protected function _afterLoad()
	{
		$items = $this->getColumnValues('string_id');
		$connection = $this->getConnection();
		if (count($items)) {
			$select = $connection->select()
			->from(array('cps'=>$this->getTable('translator/translation')), array('string_id', 'store_id'))
			->where('cps.string_id IN (?)', $items);
			if ($results = $connection->fetchAll($select)) {
				$storeIds = array();
				foreach ($results as $record) {
					if (array_key_exists($record['string_id'], $storeIds)) {
					$storeIds[$record['string_id']] .= ',' . $record['store_id'];
					} else {
						$storeIds[$record['string_id']] = $record['store_id'];
					}
				}
				foreach ($storeIds as $key => $record) {
					$storeIds[$key] = explode(',', $record);
				}
				foreach ($this as $item) {	
					if (!isset($storeIds[$item->getData('string_id')])) {
						continue;
					}
					$item->setData('store_id', $storeIds[$item->getData('string_id')]);
				}
			}
		}
	
		return parent::_afterLoad();
	}
}