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
	
	public function _forwardJob()
	{
		$register = $this->_getJobRegister();
		$element = array_pop($register->data);
		$this->_setJobRegister($register);
		
		return $element;
	}
	
	public function iterateSyncJob($action)
	{
		$output = array();
		
		switch ($action) {
			case 'translation_files_sync':
				$data = $this->_forwardJob();
				if ($data) {
					$output['data'] = $this->_checkFile($data);
					$output['state'] = true;
					break;
				} else {
					$this->_cleanJobRegister();
					$output['state'] = false;
					break;
				}
			case 'translate_path_sync':
				$register = $this->_getJobRegister();
				$output['state'] = $register->state;
				if ($output['state']) {
					$data = $register->data;
					$output['data'] = $data->message;
					if ($data->init) {
						$output['url'] = $data->path;
						$data->init = false;
						$register->data = $data;
						$this->_setJobRegister($register);
					}
					break;
				} else {
					$this->_cleanJobRegister();
					break;
				}
		}
		
		return $output;
	}
	
	public function _checkFile($data)
	{
		$collection = $this->getCollection()->load();
		$response = '';
		
		$file = Mage::getBaseDir('locale') . DS . $data->locale . DS . $data->fileName;
		if (file_exists($file)) {
			$translation_pairs = $this->_getFileData($file);
			foreach ($translation_pairs as $string => $translate) {
				$item = new Magestance_Demo_Model_Translator();
				$item->setString($string);
				$item->setTranslate($translate);
				$item->setStoreId(0);
				$item->setLocale($data->locale);
				$item->setModule($data->key);
				$item->setOrigin($file);
				$item->setTargets(array());
				$collection->addItem($item);
			}
			$response = 'Successfully scanned: ' . $file . '<br />';
		}
		$collection->save();
		
		return $response;
	}
	
	public function _initJobRegister($action, $data) {
		
		$register = array('action' => $action, 'data' => $data, 'state' => true);
		
		$this->_setJobRegister($register);
	}
	
	public function _getJobRegister() {
		$register = Mage::getModel('demo/demo')
		->load('job_register')
		->getValue();
		
		return json_decode($register);
	}
	
	public function _setJobRegister($register) {
		Mage::getModel('demo/demo')
		->load('job_register')
		->setValue(json_encode($register))
		->save();
	}
	
	public function _cleanJobRegister()
	{
		$model = Mage::getModel('demo/demo')
		->load('job_register')
		->setValue(array('state' => false))
		->save();
	}
	
	public function syncFileTranslations()
	{		
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
	    
	    $data = array();
	    $stores = Mage::app()->getLocale()->getOptionLocales();
	    foreach ($stores as $store) {
	    	if (file_exists(Mage::getBaseDir('locale') . DS . $store['value'])) {
	    		foreach ($files as $key => $file) {
	    			$data[] = array('locale' => $store['value'], 'fileName' => $file, 'key' => $key);
	    		}
	    	}
	    }
	    
	    $this->_initJobRegister('translation_files_sync', $data);
	}
	
	public function migrateDbTranslations()
	{
		$collection = $this->getCollection()->load();
		
		$model = Mage::getModel('core/translate');
		$resource = $model->getResource();
		$adapter = $this->getResource()->getReadConnection();
		
		if (!$adapter) {
			Mage::log('could not get the adapter.');
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
			Mage::log('could not get the adapter.');
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