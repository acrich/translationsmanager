<?php

class Wheelbarrow_Translator_Helper_Importer extends Mage_Core_Helper_Abstract
{
	const CSV_SEPARATOR = ',';
	
	protected function _getLocales()
	{
		$stores = Mage::app()->getStores(true);
		$locales = array();
		foreach ($stores as $store) {
			$locale = $store->getConfig('general/locale/code');
			$locales[$locale] = $locale;
		}
		return $locales;
	}
	
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
	
	public function _processCsvFile($file_description)
	{
		$data = array();
		if (file_exists($file_description['fileName'])) {
			$translation_pairs = $this->_getFileData($file_description['fileName']);
				
			foreach ($translation_pairs as $string => $translation) {
				$data[] = array(
						'string' => $string,
						'translation' => $translation,
						'locale' => $file_description['locale'],
						'fileName' => $file_description['fileName'],
						'module' => $file_description['moduleName'],
						'areas' => $file_description['areas'],
						'strict' => true
				);
			}
		}
		return $data;
	}
	
	protected function _getFilesList()
	{
		$files = array();
		foreach (array('frontend', 'adminhtml', 'install') as $area) {
			if (Mage::getConfig()->getNode($area . '/translate/modules')) {
				$modules = Mage::getConfig()->getNode($area . '/translate/modules');
				if ($modules->children()) {
					$modules = $modules->children();
					foreach ($modules as $moduleName => $info) {
						$info = $info->asArray();
						foreach ($info['files'] as $file) {
							if (!isset($files[$file])) {
								$files[$file] = array('modules' => array(), 'areas' => array());
							}
							
							$files[$file]['modules'][$moduleName] = $moduleName;
							
							if (!isset($files[$file]['areas'][$moduleName])) {
								$files[$file]['areas'][$moduleName] = array();
							}
							$files[$file]['areas'][$moduleName][] = $area;
						}
					}
				}
			}
		}
		return $files;
	}
	
	public function pushCsvFilesToQueue()
	{
		$files = $this->_getFilesList();
	
		$data = array();
		$locales = $this->_getLocales();
		$basedir = Mage::getBaseDir('locale');
		foreach ($locales as $locale) {
			if (file_exists(Mage::getBaseDir('locale') . DS . $locale)) {
				foreach ($files as $file => $file_data) {
					$fileName = $basedir . DS . $locale . DS . $file;
					foreach ($file_data['modules'] as $moduleName) {
						$pairs = $this->_processCsvFile(array(
															'locale' => $locale, 
															'fileName' => $fileName, 
															'moduleName' => $moduleName,
															'areas' => $file_data['areas'][$moduleName]
														));
						$data = array_merge($data, (array)$pairs);
					}
				}
			}
		}
		
		$batches = array_chunk($data, Mage::getStoreConfig('translator/options/batch_size'));
		foreach ($batches as $batch) {
			Mage::getModel('translator/cache')->createItem('batch', $batch);
		}
		
		Mage::helper('translator/sync')->setRegisterData(array(
				'completed' => 0, 
				'total' => count($data)
		));
	}
	
	public function getResources()
	{
		$files = $this->_getFilesList();
	
		$data = array();
		$locales = $this->_getLocales();
		$basedir = Mage::getBaseDir('locale');
		foreach ($locales as $locale) {
			if (file_exists(Mage::getBaseDir('locale') . DS . $locale)) {
				foreach ($files as $file => $file_data) {
					$fileName = $basedir . DS . $locale . DS . $file;
					foreach ($file_data['modules'] as $moduleName) {
						$data[] = serialize(array(
												'locale' => $locale,
												'fileName' => $fileName,
												'moduleName' => $moduleName,
												'areas' => $file_data['areas'][$moduleName],
												'status' => false
											));
					}
				}
			}
		}
		return $data;
	}
	
	public function pushThemeCsvsToQueue() {
		$params = array('_type' => 'locale');
    	$package = Mage::getDesign();
    	$locales = $this->_getLocales();
    	$data = array();
    	
    	foreach (array('frontend', 'adminhtml') as $area) {
    		$package->setArea($area);
			
			foreach ($locales as $locale) {
		        $params['_area'] = $package->getArea();
		        $params['_package'] = $package->getPackageName();
		        $params['_theme'] = $package->getTheme('locale');
		        
		        if (empty($params['_theme'])) {
		        	$params['_theme'] = $package->getFallbackTheme();
		        	if (empty($params['_theme'])) {
		        		$params['_theme'] = $package::DEFAULT_THEME;
		        	}
		        }

		    	$dir = Mage::getBaseDir('design').DS.
		    	$params['_area'].DS.$params['_package'].DS.$params['_theme'] . DS . 'locale' . DS .
		    	$locale;
		    	$file = $dir . DS . 'translate.csv';
		    	
		    	if (!file_exists($file)) {
		    		$params['_theme']   = $package::DEFAULT_THEME;

		    		$dir = Mage::getBaseDir('design').DS.
		    		$params['_area'].DS.$params['_package'].DS.$params['_theme'] . DS . 'locale' . DS .
		    		$locale;
		
		    		$file = $dir . DS . 'translate.csv';
		    		
		    		if (!file_exists($file)) {
		    			if ($area == 'frontend') {
		    				$params['_package'] = $package::BASE_PACKAGE;
		    			} else {
		    				$params['_package'] = $package::DEFAULT_PACKAGE;
		    			}

		    			$dir = Mage::getBaseDir('design').DS.
		    			$params['_area'].DS.$params['_package'].DS.$params['_theme'] . DS . 'locale' . DS .
		    			$locale;
		
		    			$file = $dir . DS . 'translate.csv';
		    		}
		    	}
			    $module = ($area == 'adminhtml') ? 'Mage_Adminhtml' : '';
				$pairs = $this->_processCsvFile(array('locale' => $locale, 'fileName' => $file, 'moduleName' => $module));
				$data = array_merge($data, (array)$pairs);
			}
		}
		
		$batches = array_chunk($data, Mage::getStoreConfig('translator/options/batch_size'));
		foreach ($batches as $batch) {
			Mage::getModel('translator/cache')->createItem('batch', $batch);
		}
		
		Mage::helper('translator/sync')->setRegisterData(array(
				'completed' => 0, 
				'total' => count($data)
		));
	}
}