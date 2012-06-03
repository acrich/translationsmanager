<?php

class Magestance_Demo_Model_Translator extends Mage_Core_Model_Abstract
{
	const CSV_SEPARATOR     = ',';
	
	const FILES_SYNC_JOB 	= 'translation_files_sync';
	
	public function _construct()
	{
		parent::_construct();
		$this->_init('demo/translator');
	}
	
	
	/**
	 * Retrieve data from file
	 *
	 * @param   string $file
	 * @return  array
	 */
	protected function _getFileData($file)
	{
		$data = array();
		if (file_exists($file)) {
			$parser = new Varien_File_Csv();
			$parser->setDelimiter(self::CSV_SEPARATOR);
			$data = $parser->getDataPairs($file);
		}
		return $data;
	}
	
	public function _forwardJob($register)
	{
		$model = Mage::getModel('demo/demo')->load($register);
		$data = json_decode($model->getValue());
		$element = array_pop($data);
		$model->setValue(json_encode($data))->save();
		return $element;
	}
	
	public function _iterateSyncJob($job)
	{
		$data = $this->_forwardJob($job);
		
		if ($data) {
			switch ($job) {
				case 'translation_files_sync':
					$this->_checkFile($data);
					break;
			}
			return array('state' => true);
		} else {
			return array('state' => false);
		}
	}
	
	public function _checkFile($data)
	{
		$collection = Mage::getModel('demo/translator')->getCollection();
		
		$file = Mage::getBaseDir('locale') . DS . $data->locale . DS . $data->fileName;
		if (file_exists($file)) {
			$translation_pairs = $this->_getFileData($file);
			foreach ($translation_pairs as $string => $translate) {
				$item = new Magestance_Demo_Model_Translator();
				$item->setString($string);
				$item->setTranslate($translate);
				$item->setStoreId(0);
				$item->setLocale($locale);
				$collection->addItem($item);
			}
		}
		$collection->save();
	}
	
	public function _syncFileTranslations()
	{
		$registry = array();
		
		$areas = array('frontend', 'adminhtml', 'install');
	    $files = array();
	    foreach ($areas as $area) {
	    	if (Mage::getConfig()->getNode($area . '/translate/modules')) {
	    		$modules = Mage::getConfig()->getNode($area . '/translate/modules');
	    		if ($modules->children()) {
	    			$modules = $modules->children();
	    			foreach ($modules as $moduleName => $info) {
	    				$info = $info->asArray();
	    				foreach ($info['files'] as $file) {
	    					$files[$moduleName] = $file;
	    				}
	    			}
	    		}
	    	}
	    }
	    
	    $stores = Mage::app()->getLocale()->getOptionLocales();
	    foreach ($stores as $store) {
	    	if (file_exists(Mage::getBaseDir('locale') . DS . $store['value'])) {
	    		foreach ($files as $file) {
	    			$registry[] = array('locale' => $store['value'], 'fileName' => $file);
	    		}
	    	}
	    }
	    
	    Mage::getModel('demo/demo')->load('translation_files_sync')->setValue(json_encode($registry))->save();
	}
	
	public function _migrateDbTranslations()
	{
		$collection = $this->getCollection();
		
		$model = Mage::getModel('core/translate');
		$resource = $model->getResource();
		$adapter = $this->getResource()->getReadConnection();
		
		if (!$adapter) {
			Mage::log('could not get the adapter.', null, 'shay.log');
			return array();
		}
		
		$select = $adapter->select()
		->from('core_translate', array('*'));
		
		$data = $adapter->fetchAll($select);
		
		foreach ($data as $entity) {
			$item = new Magestance_Demo_Model_Translator();
			$item->setString($entity['string']);
			$item->setKeyID($entity['key_id']);
			$item->setTranslate($entity['translate']);
			$item->setStoreId($entity['store_id']);
			$item->setLocale($entity['locale']);
			$collection->addItem($item);
		}
		$collection->save();
		
		return $this;
	}
	
	public function _getAggregatedCollection()
	{
		$collection = $this->getCollection()->addAttributeToSelect('*');
		/*
		$model = Mage::getModel('core/translate');
		$resource = $model->getResource();
		
		$adapter = $this->getResource()->getReadConnection();
		if (!$adapter) {
			Mage::log('could not get the adapter.', null, 'shay.log');
			return array();
		}
		
		$select = $adapter->select()
			->from($resource->getMainTable(), array('*'));
		
		$data = $adapter->fetchAll($select);
		
		foreach ($data as $entity) {
			$item = new Magestance_Demo_Model_Translator();
			$item->setString($entity['string']);
			$item->setKeyID($entity['key_id']);
			$item->setTranslate($entity['translate']);
			$item->setStoreId($entity['store_id']);
			$item->setLocale($entity['locale']);
			$collection->addItem($item);
		}
		*/
		return $collection;
	}
}