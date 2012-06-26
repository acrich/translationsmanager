<?php

class Magestance_Demo_Helper_Importer extends Mage_Core_Helper_Abstract
{
	const CSV_SEPARATOR = ',';
	
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
	
	protected function _processCsvFile($file_description)
	{
		$data = array();
		
		$file = Mage::getBaseDir('locale') . DS . $file_description['locale'] . DS . $file_description['fileName'];
		if (file_exists($file)) {
			$translation_pairs = $this->_getFileData($file);
				
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
		$locales = Mage::app()->getLocale()->getOptionLocales();
		foreach ($locales as $locale) {
			if (file_exists(Mage::getBaseDir('locale') . DS . $locale['value'])) {
				foreach ($files as $moduleName => $file) {
					$pairs = $this->_processCsvFile(array('locale' => $locale['value'], 'fileName' => $file, 'moduleName' => $moduleName));
					$data = array_merge($data, (array)$pairs);
				}
			}
		}
		
		$sync = Mage::helper('demo/sync');
		$queue = Mage::helper('demo/queue');
		
		$queue->setFirst($sync::CSV_QUEUE_NAME, $data);
		$queue->prepareBatches($sync::CSV_QUEUE_NAME, 30);
		
		$queue->setRegisterData('sync', array(
				'completed' => 0, 
				'total' => count($data)
		));
	}
}