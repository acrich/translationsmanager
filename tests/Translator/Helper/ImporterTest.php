<?php
require_once 'PHPUnit/Autoload.php';
require_once '/var/www/translator/app/Mage.php';
 
class Wheelbarrow_Translator_Helper_Importer_Test extends PHPUnit_Framework_TestCase {
	
	public function log($message)
	{
		$message = file_get_contents('shay.log')."\n"."\n".'Message: '.json_encode($message);
		file_put_contents('shay.log', $message);
	}
	
	public function setUp()
	{
		Mage::app('default');
		$this->importer = Mage::helper('translator/importer');
	}

	/**
	 * Tests the CSV translation file processing function.
	 */
	public function testCsvProcessing()
	{
		$file_description = array(
					'fileName' => 'tests/Translator/Helper/translations.csv',
					'locale' => 'he_IL',
					'moduleName' => 'Wheelbarrow_Test',
					'areas' => array('frontend', 'install')
				);
		$expected = array(
					0 => array(
								'string' => 'one',
								'translation' => 'un',
								'locale' => 'he_IL',
								'fileName' => 'tests/Translator/Helper/translations.csv',
								'module' => 'Wheelbarrow_Test',
								'areas' => array('frontend', 'install'),
								'strict' => true
							),
					1 => array(
							'string' => 'two',
							'translation' => 'deux',
							'locale' => 'he_IL',
							'fileName' => 'tests/Translator/Helper/translations.csv',
							'module' => 'Wheelbarrow_Test',
							'areas' => array('frontend', 'install'),
							'strict' => true							
							),
					2 => array(
							'string' => 'three',
							'translation' => 'trois',
							'locale' => 'he_IL',
							'fileName' => 'tests/Translator/Helper/translations.csv',
							'module' => 'Wheelbarrow_Test',
							'areas' => array('frontend', 'install'),
							'strict' => true							
							)
				);
		$actual = $this->importer->_processCsvFile($file_description);
		$this->assertEquals($expected, $actual);
	}
	
	/**
	 * 
	 * Test scanning module CSV files and batching up the translation pairs.
	 * 
	 * We're using Mage_Core's translate model to check whether it behaves as it would
	 * have without this module.
	 * 
	 * The function covers:
	 *  - Multiple modules (Mage_Sales, Mage_Tax)
	 *  - Multiple locales (en_US, fr_FR)
	 *  - Multiple areas (frontend, adminhtml)
	 *  - More than one batch
	 */
	public function testModuleTranslations()
	{
		$translator = new Mage_Core_Model_Translate();
		$locale = $translator->getLocale();
		$translator_data = $translator->init('frontend')->getData();

		$this->importer->pushCsvFilesToQueue();
		
		$batches = Mage::getModel('translator/cache')->getCollection()
			->addFieldToFilter('name', 'batch')
			->load();
		
		$batches_data = array();
		foreach ($batches as $batch) {
			$pairs = unserialize($batch->getRegister());
			foreach ($pairs as $pair) {
				if ($pair['locale'] == $locale) {
					$batches_data[$pair['string']] = $pair['translation'];
				}
			}
		}

		$this->assertEquals($translator_data, $batches_data);
		
		$locale = 'fr_FR';
		$translator->setLocale($locale);
		$translator_data = $translator->init('frontend')->getData();
		
		$batches_data = array();
		foreach ($batches as $batch) {
			$pairs = unserialize($batch->getRegister());
			foreach ($pairs as $pair) {
				if ($pair['locale'] == $locale && $pair['module'] == 'Mage_Sales') {
					$batches_data[$pair['module'].'::'.$pair['string']] = $pair['translation'];
				}
			}
		}
		
		$this->assertEquals($translator_data, $batches_data);
		
		$translator_data = $translator->init('adminhtml')->getData();
		$batches_data = array();
		foreach ($batches as $batch) {
			$pairs = unserialize($batch->getRegister());
			foreach ($pairs as $pair) {
				if ($pair['locale'] == $locale) {
					$batches_data[$pair['module'].'::'.$pair['string']] = $pair['translation'];
				}
			}
		}
		
		$this->assertEquals($translator_data, $batches_data);
	}
	
	/**
	 * 
	 * Tests the resources scanning function.
	 * 
	 * The function tests that:
	 *  - Both locales that have files are scanned ('default', 'french').
	 *  - The locale that exists but doesn't have a file is ignored ('madeup').
	 *  - The he_IL folder that doesn't account for any configured store view is ignored.
	 *  - The Mage_Tax adminhtml translations use the Mage_Sales.csv file, so those items will
	 *  	be saved twice.
	 * 
	 */
	public function testResourcesGetter()
	{
		$expected = array(
					0 => serialize(
								array(
									'locale' => 'en_US',
									'fileName' => Mage::getBaseDir('locale') . DS . 'en_US' . DS . 'Mage_Sales.csv',
									'moduleName' => 'Mage_Sales',
									'areas' => array('frontend', 'adminhtml'),
									'status' => false
								)
							),
					1 => serialize(
							array(
									'locale' => 'fr_FR',
									'fileName' => Mage::getBaseDir('locale') . DS . 'fr_FR' . DS . 'Mage_Sales.csv',
									'moduleName' => 'Mage_Sales',
									'areas' => array('frontend', 'adminhtml'),
									'status' => false
							)
					),
					2 => serialize(
							array(
									'locale' => 'en_US',
									'fileName' => Mage::getBaseDir('locale') . DS . 'en_US' . DS . 'Mage_Sales.csv',
									'moduleName' => 'Mage_Tax',
									'areas' => array('adminhtml'),
									'status' => false
							)
					),
					3 => serialize(
							array(
									'locale' => 'fr_FR',
									'fileName' => Mage::getBaseDir('locale') . DS . 'fr_FR' . DS . 'Mage_Sales.csv',
									'moduleName' => 'Mage_Tax',
									'areas' => array('adminhtml'),
									'status' => false
							)
					)
				);		
		$resources = $this->importer->getResources();
		
		foreach ($expected as $item) {
			$this->assertContains($item, $resources);
		}
		$this->assertCount(count($expected), $resources);
	}
	
	public function getBatchPairs()
	{
		$batches = Mage::getModel('translator/cache')->getCollection()
			->addFieldToFilter('name', 'batch')
			->load();
		
		$batches_data = array();
		foreach ($batches as $batch) {
			$pairs = unserialize($batch->getRegister());
			$batches_data = array_merge($batches_data, $pairs);
			$batch->delete();
		}
		return $batches_data;
	}
	
	public function removeThemeFile($area, $package, $theme, $locale)
	{
		$file = Mage::getBaseDir('design') .DS.
				$area .DS.
				$package .DS.
				$theme . DS .
				'locale' . DS .
				$locale . DS .
				'translate.csv';
		if (file_exists($file)) {
			unlink($file);
		}
	}
	
	public function createThemeFile($area, $package, $theme, $locale, $sample_data)
	{
		$file = Mage::getBaseDir('design') .DS.
				$area .DS.
				$package .DS.
				$theme . DS .
				'locale' . DS .
				$locale . DS .
				'translate.csv';
		touch($file);
		
		if (file_exists($file)) {
			file_put_contents($file, $sample_data);
			return $file;
		}
		
		$this->fail('Could not create file.');
	}
	
	public function getExpected($sample_data, $file_name, $area, $locale)
	{
		preg_match_all('/(.*)\n(.*)/U', $sample_data, $matches);
		$expected = array();
		foreach ($matches[1] as $match) {
			$pair = explode(',',$match);
			$expected[] = array(
					'string' => $pair[0],
					'translation' => $pair[1],
					'locale' => $locale,
					'fileName' => $file_name,
					'module' => ($area == 'adminhtml') ? 'Mage_Adminhtml' : '',
					'areas' => null,
					'strict' => true
			);
		}
		return $expected;
	}
	
	/**
	 *
	 * Test scanning theme CSV files and batching up the translation pairs.
	 *
	 * We're using Mage_Core's translate model to check whether it behaves as it would
	 * have without this module.
	 * We're using both frontend and adminhtml files as the function should cover both with each run.
	 * 
	 * @todo refactor deplicate code.
	 */
	public function testThemeTranslations()
	{
		//With configured theme.
		$sample_data = "one,un"."\n"."two,deux"."\n";
		$file_name = $this->createThemeFile('frontend', 'custom', 'modern', 'en_US', $sample_data);
		
		$expected = $this->getExpected($sample_data, $file_name, 'frontend', 'en_US');
		
		$sample_data = "three,trois"."\n"."four,quatre"."\n";
		$file_name = $this->createThemeFile('adminhtml', 'default', 'default', 'en_US', $sample_data);
		
		$expected = array_merge($expected, $this->getExpected($sample_data, $file_name, 'adminhtml', 'en_US'));
		
		$this->importer->pushThemeCsvsToQueue();
		$batches_data = $this->getBatchPairs();
		
		$this->assertEquals($expected, $batches_data);
		
		$this->removeThemeFile('frontend', 'custom', 'modern', 'en_US');
		$this->removeThemeFile('adminhtml', 'default', 'default', 'en_US');
		
		
		//With default theme.
		$sample_data = "six,six"."\n"."seven,sept"."\n";
		$file_name = $this->createThemeFile('frontend', 'custom', 'default', 'en_US', $sample_data);
		
		$expected = $this->getExpected($sample_data, $file_name, 'frontend', 'en_US');
		
		$sample_data = "eight,huit"."\n"."nine,neuf"."\n";
		$file_name = $this->createThemeFile('adminhtml', 'default', 'default', 'en_US', $sample_data);
		
		$expected = array_merge($expected, $this->getExpected($sample_data, $file_name, 'adminhtml', 'en_US'));
		
		$this->importer->pushThemeCsvsToQueue();
		$batches_data = $this->getBatchPairs();
		
		$this->assertEquals($expected, $batches_data);

		$this->removeThemeFile('frontend', 'custom', 'default', 'en_US');
		$this->removeThemeFile('adminhtml', 'default', 'default', 'en_US');

		//With a configured theme that doesn't have a translate.csv file.		
		$sample_data = "three,trois"."\n"."four,quatre"."\n";
		$file_name = $this->createThemeFile('adminhtml', 'default', 'default', 'en_US', $sample_data);
		
		$expected = $this->getExpected($sample_data, $file_name, 'adminhtml', 'en_US');
		
		$this->importer->pushThemeCsvsToQueue();
		$batches_data = $this->getBatchPairs();
		
		$this->assertEquals($expected, $batches_data);
		
		$this->removeThemeFile('adminhtml', 'default', 'default', 'en_US');
		
		//With only a base package translate.csv file.
		$sample_data = "six,six"."\n"."seven,sept"."\n";
		$file_name = $this->createThemeFile('frontend', 'base', 'default', 'en_US', $sample_data);
		
		$expected = $this->getExpected($sample_data, $file_name, 'frontend', 'en_US');
		
		$sample_data = "eight,huit"."\n"."nine,neuf"."\n";
		$file_name = $this->createThemeFile('adminhtml', 'default', 'default', 'en_US', $sample_data);
		
		$expected = array_merge($expected, $this->getExpected($sample_data, $file_name, 'adminhtml', 'en_US'));
		
		$this->importer->pushThemeCsvsToQueue();
		$batches_data = $this->getBatchPairs();
		
		$this->assertEquals($expected, $batches_data);
		
		$this->removeThemeFile('frontend', 'base', 'default', 'en_US');
		$this->removeThemeFile('adminhtml', 'default', 'default', 'en_US');
		
		//Without any translate.csv files at all.
		$this->importer->pushThemeCsvsToQueue();
		$batches_data = $this->getBatchPairs();
		
		$this->assertEquals(array(), $batches_data);
	}
	
	public function tearDown()
	{
		//Remove the batch item we created before.
		Mage::getModel('translator/cache')->getCollection()
			->addFieldToFilter('name', 'batch')
			->getLastItem()
			->delete();

		//Remove empty items created because we don't do checks when saving to the register in the
		// sync helper functions.
		//@todo Once it's fixed, remove this.
		foreach (Mage::getModel('translator/cache')->getCollection()->load() as $item)
			if ($item->getName() == null)
				$item->delete();
	}
}