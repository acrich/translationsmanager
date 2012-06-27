<?php

class Magestance_Translator_Helper_Importer extends Mage_Core_Helper_Abstract
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
	
	protected function _processCsvFile($file_description)
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
						'module' => $file_description['moduleName']
				);
			}
		}
		return $data;
	}
	
	public function pushCsvFilesToQueue()
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
							$files[$moduleName] = $file;
						}
					}
				}
			}
		}
	
		$data = array();
		$locales = $this->_getLocales();
		$basedir = Mage::getBaseDir('locale');
		foreach ($locales as $locale) {
			if (file_exists(Mage::getBaseDir('locale') . DS . $locale)) {
				foreach ($files as $moduleName => $file) {
					$fileName = $basedir . DS . $locale . DS . $file;
					$pairs = $this->_processCsvFile(array('locale' => $locale, 'fileName' => $fileName, 'moduleName' => $moduleName));
					$data = array_merge($data, (array)$pairs);
				}
			}
		}
		
		$sync = Mage::helper('translator/sync');
		$queue = Mage::helper('translator/queue');
		
		$queue->setFirst($sync::CSV_QUEUE_NAME, $data);
		$queue->prepareBatches($sync::CSV_QUEUE_NAME, 30);
		
		$queue->setRegisterData('sync', array(
				'completed' => 0, 
				'total' => count($data)
		));
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
		
		$sync = Mage::helper('translator/sync');
		$queue = Mage::helper('translator/queue');
		
		$queue->setFirst($sync::THEME_QUEUE_NAME, $data);
		$queue->prepareBatches($sync::THEME_QUEUE_NAME, 30);
		
		$queue->setRegisterData('sync', array(
				'completed' => 0,
				'total' => count($data)
		));
	}
}