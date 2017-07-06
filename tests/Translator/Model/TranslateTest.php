<?php
require_once 'PHPUnit/Autoload.php';
require_once '/var/www/translator/app/Mage.php';
 
class Wheelbarrow_Translator_Model_Translate_Test extends PHPUnit_Framework_TestCase {
	
	public function log($message)
	{
		$message = file_get_contents('shay.log')."\n"."\n".'Message: '.json_encode($message);
		file_put_contents('shay.log', $message);
	}
	
	public function setUp()
	{
		Mage::app('default');
		$this->model = Mage::getModel('translator/translate');
	}
	
	public function testInit()
	{
		// - both init functions return the same values for the frontend area:
		
		//Init the core translate model for for the en_US locale:
		$core_translate = new Mage_Core_Model_Translate();
		$core_translate->setLocale('en_US');
		$core_translate->init('frontend');
		$expected = $core_translate->getData();		
		
		//Init the overwritten model for the en_US locale:
		$this->model = new Wheelbarrow_Translator_Model_Translate();
		$this->model->setLocale('en_US');
		$this->model->init('frontend');
		$actual = $this->model->getData();
		
		//Test that both have the same amount of items:
		$this->assertEquals(count($expected), count($actual));
		
		//Test that the string is really in there:
		$this->assertEquals('Fuck off', $actual['Leave']);
		$this->assertEquals('Fuck off', $expected['Leave']);
		
		//Init the core translate model for for the fr_FR locale:
		$core_translate = new Mage_Core_Model_Translate();
		$core_translate->setLocale('fr_FR');
		$core_translate->init('frontend');
		$expected = $core_translate->getData();
		
		//Init the overwritten model for the fr_FR locale:
		$this->model = new Wheelbarrow_Translator_Model_Translate();
		$this->model->setLocale('fr_FR');
		$this->model->init('frontend');
		$actual = $this->model->getData();
		
		//Test that both have the same amount of items:
		$this->assertEquals(count($expected), count($actual));
		
		//Test that the string is really in there:
		$this->assertEquals('Sortir', $actual['Leave']);
		$this->assertEquals('Sortir', $expected['Leave']);
		
		// - Testing the same this with adminhtml:
		
		//Init the core translate model for for the en_US locale:
		$core_translate = new Mage_Core_Model_Translate();
		$core_translate->setLocale('en_US');
		$core_translate->init('adminhtml');
		$expected = $core_translate->getData();
		
		//Init the overwritten model for the en_US locale:
		$this->model = new Wheelbarrow_Translator_Model_Translate();
		$this->model->setLocale('en_US');
		$this->model->init('adminhtml');
		$actual = $this->model->getData();
		
		//Test that both have the same amount of items:
		$this->assertEquals(count($expected), count($actual));
		
		//Test that the string is really in there:
		$this->assertEquals('Fuck off', $actual['Leave']);
		$this->assertEquals('Fuck off', $expected['Leave']);
		$this->assertEquals('Fuck off', $actual['Mage_Sales::Leave']);
		$this->assertEquals('Fuck off', $actual['Mage_Tax::Leave']);
		$this->assertEquals('Fuck off', $expected['Mage_Sales::Leave']);
		$this->assertEquals('Fuck off', $expected['Mage_Tax::Leave']);
		
		//Init the core translate model for for the fr_FR locale:
		$core_translate = new Mage_Core_Model_Translate();
		$core_translate->setLocale('fr_FR');
		$core_translate->init('adminhtml');
		$expected = $core_translate->getData();
		
		//Init the overwritten model for the fr_FR locale:
		$this->model = new Wheelbarrow_Translator_Model_Translate();
		$this->model->setLocale('fr_FR');
		$this->model->init('adminhtml');
		$actual = $this->model->getData();
		
		//Test that both have the same amount of items:
		$this->assertEquals(count($expected), count($actual));
		
		//Test that the string is really in there:
		$this->assertEquals('Sortir', $actual['Leave']);
		$this->assertEquals('Sortir', $expected['Leave']);
		$this->assertEquals('Sortir', $actual['Mage_Sales::Leave']);
		$this->assertEquals('Sortir', $actual['Mage_Tax::Leave']);
		$this->assertEquals('Sortir', $expected['Mage_Sales::Leave']);
		$this->assertEquals('Sortir', $expected['Mage_Tax::Leave']);

		// - item created for both areas, init function has it in its _data array.
		
		//Create the translate model:
		$this->model = new Wheelbarrow_Translator_Model_Translate();
		$this->model->setLocale('en_US');
		$this->model->init('frontend');
		
		//Make sure its empty:
		$actual = $this->model->getData();
		$this->assertEquals(1, count($actual));
		$this->assertEquals('Fuck off', $actual['Leave']);
		
		//Set items:
		$ids = array();
		$data = array(
					0 => array(
						'string' => 'test', 
						'module' => 'Wheelbarrow_Test',
						'translation' => 'translated test',
						'locale' => 'en_US',
						'store_id' => 1
					),
					1 => array(
						'string' => 'temp',
						'module' => 'Wheelbarrow_Temp',
						'translation' => 'translated temp',
						'locale' => 'en_US',
						'store_id' => 0
					),
					2 => array(
						'string' => 'test',
						'module' => 'Wheelbarrow_Test',
						'translation' => 'examen',
						'locale' => 'fr_FR',
						'store_id' => 2
				));
		
		$ids = array();
		foreach($data as $item) {
			$ids[] = $this->model->addEntry($item);
		}
		
		//Init it again, so it'll populate the values:
		$this->model->init('frontend');

		//Test that it has the values:
		$actual = $this->model->getData();
		$this->assertEquals(3, count($actual));
		//this one now has a scope, because _scopeData had the item from before:
		$this->assertEquals('Fuck off', $actual['Mage_Sales::Leave']);
		$this->assertEquals('translated test', $actual['test']);
		$this->assertEquals('translated temp', $actual['temp']);
		
		//Cleaning the items:
		foreach ($ids as $id) {
			Mage::getModel('translator/string')->load($id)->delete();
		}
	}
	
	public function resetModel()
	{
		$this->model = new Wheelbarrow_Translator_Model_Translate();
		$this->model->setTranslateInline(false);
		$this->model->init('frontend');
	}
	
	/**
	 * 
	 * - Observer flag set, text is a string, item already exists
	 * 	-> It should only create a path item, and it shouldn't override any values in the string item.
	 * - Observer flag set, text is an object, item already exists, path already exists
	 * 	-> It shouldn't change anything in any of those items.
	 * - Observer flag set, text is an object, item doesn't exist
	 * 	-> It should create both string and path items.
	 * - Observer flag set, text is an empty string
	 * 	-> It shouldn't do anything.
	 * 
	 * - text is an object, there's an item that corresponds to it.
	 * 	-> should translate.
	 * - text is an object with a module, there's an item that has the same string and no module
	 * 	-> should translate.
	 * - text is an object, there's an item that has the same string and a different module
	 * 	-> should not translate.
	 * - text is an object, there isn't an item that fits.
	 * 	-> should not translate.
	 * - text is a string, and there's an item that fits with no module
	 *  -> should translate.
	 * - text is a string, and there isn't an item that fits.
	 * 	-> should not translate.
	 * 
	 * - text has a matching item, but with the wrong store view.
	 * 	-> should not translate.
	 * - test has a matching item that's in the admin store view.
	 * 	-> should translate.
	 * 
	 * - There is an item, but its status is set to false, and there is parameter data to process.
	 * 	-> should not translate.
	 * - There is an item, and its status is set to true, and there is parameter data to process.
	 * 	-> should translate with the parameter changes.
	 * 
	 * - Parameters attribute is empty.
	 * 	-> should translate without any parameter changes.
	 * 
	 * - String has 1 param. Param at position 0 takes value from code at position 1
	 * 	-> value should be empty.
	 * - String has 1 param. Param at position 1 takes value from code at position 0
	 * 	-> position 0 hasn't been overridden so it'll still work.
	 * - String has 1 param. Param at position 0 takes value from code at position 0, value set.
	 * 	-> value should be filled.
	 * - String has 2 params. Param code positions are reversed.
	 * 	-> the values should fill out in reverse order.
	 * - String has 1 param. Param at position 1 has a value.
	 * 	-> value at position 0 hasn't been modified so it'll still work.
	 * - String has 1 param. Param at position 0 has a value.
	 * 	-> value should be filled.
	 * - String has 1 param. Param at position 0 is an empty string.
	 * 	-> value should be empty.
	 * - String has 2 params. Only param at position 1 set, hardcoded to code position 0.
	 * 	-> both should be filled with the value from code position 0, because only the second was modified.
	 * - String has 2 params. Only param at position 0 set, hardcoded to code position 1.
	 * 	-> same as the last scenario just in reverse.
	 * - String has 2 params. Only param at position 0 set, has a value.
	 * 	-> First param will be set from the attribute, the second from the hardcoded values.
	 * - String has 2 params. Only param at position 1 set, has a value.
	 * 	-> First param will be set from the hardcoded values, the second from the attributes.
	 * - String has 1 param. 2 params set with values, at positions 0 and 1.
	 * 	-> only the value from position 0 should appear.
	 * 
	 * - Value is a regular string.
	 * 	-> value should appear as is.
	 * - Value has a parameter.
	 * 	-> parameter should appear as is.
	 * - Value contains a custom variable and plain text.
	 * 	-> custom variable should be parsed, plain text should appear as is.
	 * - Value contains 2 custom variables with text in between and around.
	 * 	-> custom variables should parse and the plain text should appear as is.
	 * 
	 * - String has 1 param. The param set has the wrong type.
	 * 	-> the value should not fill from the attributes
	 * 
	 * - String has quotes or double-quotes.
	 * 	-> It should properly identify the string and translate it.
	 * - String has backdashes.
	 * 	-> It should properly identify the string and translate it.
	 * 
	 * - Custom variable exists but is in a different store than the one set.
	 * 	-> the custom variable should return the default value.
	 * - Custom variable exists and set to the right store view.
	 * 	-> the value should be filled.
	 * - Custom variable doesn't exist.
	 * 	-> the value should be empty.
	 * 
	 */
	public function testTranslate()
	{
		//Preparing default data:
		
		//Setting observer flag:
		$_GET[Mage::getModel('translator/observer')->getObserverFlag()] = true;
		
		//Creating the expected item:
		$expected = array(
				'string' => 'test',
				'module' => 'Wheelbarrow_Test',
				'translation' => 'translated test',
				'locale' => 'en_US',
				'store_id' => 0
		);
		
		//Creating the expected translation $args:
		$text = new Mage_Core_Model_Translate_Expr($expected['string'], $expected['module']);
		
		//Resetting the model:
		$this->resetModel();
		
		//- Observer flag set, text is a string, item already exists
		//	-> It should only create a path item, and it shouldn't override any values in the string item.
		
		//Creating the item:
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text->getText()));
		
		//Check that nothing was overridden in the original item:
		$string_item = Mage::getModel('translator/string')->load($string_id);
		$this->assertEquals($expected['module'], $string_item->getModule());
		
		//Check that a path item was created:
		$paths = Mage::getModel('translator/path')->getPathIdsByStringId($string_id);
		$this->assertEquals(1, count($paths));
		
		//Cleaning:
		$string_item->delete();
		Mage::getModel('translator/path')->load(array_pop($paths))->delete();
		$this->resetModel();

		//Observer flag set, text is an object, item already exists, path already exists
		// 	-> It shouldn't change anything in any of those items.
		
		//Create the items:
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		$expected_path = array(
					'path' => 'some path',
					'string_id' => $string_id,
					'file' => 'testFile.php',
					'offset' => 1
				);
		$path_id = Mage::getModel('translator/path')->createItem($expected_path);
		
		//Translate the string again:
		$this->model->translate(array($text));
		
		//See that nothing got changed:
		$this->assertEquals(Mage::getModel('translator/path')->load($path_id)->getFile(), $expected_path['file']);
		
		//Cleaning:
		Mage::getModel('translator/path')->load($path_id)->delete();
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
		//- Observer flag set, text is an object, item doesn't exist
		// 	-> It should create both string and path items.
		
		//Remove the existing items:
		Mage::getModel('translator/path')->load($path_id)->delete();
		$string_item->delete();
		
		//Make sure there aren't any items:
		$this->assertEquals(0, count(Mage::getModel('translator/string')->getCollection()->load()));
		$this->assertEquals(0, count(Mage::getModel('translator/path')->getCollection()->load()));
		
		//Translate the string again:
		$this->model->translate(array($text));
		
		//Check that it created both the string and path items:
		$this->assertEquals(1, count(Mage::getModel('translator/string')->getCollection()->load()));
		$this->assertEquals(1, count(Mage::getModel('translator/path')->getCollection()->load()));
		
		//Cleaning:
		Mage::getModel('translator/path')->getCollection()->getFirstItem()->delete();
		Mage::getModel('translator/string')->getCollection()->getFirstItem()->delete();
		$this->resetModel();
		
		//- Observer flag set, text is an empty string
	 	// 	-> It shouldn't do anything.
	 	
		//Check that there aren't any items:
		$this->assertEquals(0, count(Mage::getModel('translator/string')->getCollection()->load()));
		
		//Translate the string again:
		$text->setText('');
		$this->model->translate(array($text));
		
		//Check that it didn't create a new item:
		$this->assertEquals(0, count(Mage::getModel('translator/string')->getCollection()->load()));
		
		//Cleaning:
		$text->setText('test');
		
		//This concludes the tests for the path scan section.
		//Removing the $_GET parameter for the rest of these tests:
		unset($_GET[Mage::getModel('translator/observer')->getObserverFlag()]);
		
		//- text is an object, there's an item that corresponds to it.
	 	// 	-> should translate.
	 	
		//Creating the item:
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text));
		
		//Checking the translation:
		$this->assertEquals($expected['translation'], $actual);
		
		//Cleaning:
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
		// - text is an object with a module, there's an item that has the same string and no module
		// 	-> should translate.
		
		//Creating the item:
		unset($expected['module']);
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text));
		
		//Checking the translation:
		$this->assertEquals($expected['translation'], $actual);
		
		//Cleaning:
		$expected['module'] = 'Wheelbarrow_Test';
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
		// - text is an object, there's an item that has the same string and a different module
		// 	-> should translate.
		
		//Creating the item:
		$expected['module'] = 'Wheelbarrow_Temp';
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text));
		
		//Checking that it translated:
		$this->assertEquals($expected['translation'], $actual);
		
		//Cleaning:
		$expected['module'] = 'Wheelbarrow_Test';
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
		// - text is an object, there isn't an item that fits.
		// 	-> should not translate.
		
		//Translating:
		$actual = $this->model->translate(array($text));
		
		//Checking that it didn't translate:
		$this->assertEquals($expected['string'], $actual);
		
		//Cleaning:
		$expected['module'] = 'Wheelbarrow_Test';
		
		// - text is a string, and there's an item that fits with no module
		//  -> should translate.
		
		//Creating the item:
		unset($expected['module']);
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text->getText()));
		
		//Checking the translation:
		$this->assertEquals($expected['translation'], $actual);
		
		//Cleaning:
		$expected['module'] = 'Wheelbarrow_Test';
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
		// - text is a string, and there isn't an item that fits.
		// 	-> should not translate.
		
		//Translating:
		$actual = $this->model->translate(array($text->getText()));
		
		//Checking that it didn't translate:
		$this->assertEquals($expected['string'], $actual);
		
		//Cleaning:
		$expected['module'] = 'Wheelbarrow_Test';
		
		// - text has a matching item, but with the wrong store view.
		// 	-> should not translate.
		
		$stores = Mage::app()->getStores(true);
		
		//Creating the item:
		$expected['store_id'] = 1;
		$string_id = $this->model->addEntry($expected);
		Mage::app()->setCurrentStore($stores[2]);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text));
		
		//Checking that it didn't translate:
		$this->assertEquals($expected['string'], $actual);
		
		//Cleaning:
		Mage::app()->setCurrentStore($stores[0]);
		$expected['store_id'] = 0;
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();	
		
		// - test has a matching item that's in the admin store view.
		// 	-> should translate.
		
		//Creating the item:
		$string_id = $this->model->addEntry($expected);
		Mage::app()->setCurrentStore($stores[2]);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text));
		
		//Checking the translation:
		$this->assertEquals($expected['translation'], $actual);
		
		//Cleaning:
		Mage::app()->setCurrentStore($stores[0]);
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
		//Starting parameter testing, and resetting the string and translation values accordingly:
		$expected['string'] = 'test %s';
		$expected['translation'] = 'translated test %s';
		$text = new Mage_Core_Model_Translate_Expr($expected['string'], $expected['module']);
		
		// - There is an item, but its status is set to false, and there is parameter data to process.
		// 	-> should not translate.
		
		//Creating the item:
		$expected['status'] = false;
		$expected['parameters'] = array(array('hardcoded' => false, 'code_position' => 0, 'position' => 0, 'value' => 'param value'));
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text));
		
		//Checking that it didn't translate:
		$this->assertEquals($expected['string'], $actual);
		
		//Cleaning:
		unset($expected['status']);
		unset($expected['parameters']);
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
		// - There is an item, and its status is set to true, and there is parameter data to process.
		// 	-> should translate with the parameter changes.
		
		//Creating the item:
		$expected['parameters'] = array(array('hardcoded' => false, 'code_position' => 0, 'position' => 0, 'value' => 'param value'));
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text));
		
		//Checking the translation:
		$this->assertEquals('translated test param value', $actual);
		
		//Cleaning:
		unset($expected['parameters']);
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();		

		
		// - Parameters attribute is empty.
		// 	-> should translate without any parameter changes.
		
		//Creating the item:
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text));
		
		//Checking the translation:
		$this->assertEquals('translated test ', $actual);
		
		//Cleaning:
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();	

		// - String has 1 param. Param at position 0 takes value from code at position 1
		// 	-> value should be empty.
		
		//Creating the item:
		$expected['parameters'] = array(array('hardcoded' => true, 'code_position' => 1, 'position' => 0, 'value' => 'param value'));
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text, 'replacement text'));
		
		//Checking the translation:
		$this->assertEquals('translated test ', $actual);
		
		//Cleaning:
		unset($expected['parameters']);
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
		
		// - String has 1 param. Param at position 1 takes value from code at position 0
		// 	-> position 0 hasn't been overridden so it'll still work.
		
		//Creating the item:
		$expected['parameters'] = array(array('hardcoded' => true, 'code_position' => 0, 'position' => 1, 'value' => 'param value'));
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text, 'replacement text'));
		
		//Checking the translation:
		$this->assertEquals('translated test replacement text', $actual);
		
		//Cleaning:
		unset($expected['parameters']);
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();	
		
		// - String has 1 param. Param at position 0 takes value from code at position 0, value set.
		// 	-> value should be filled.
		
		//Creating the item:
		$expected['parameters'] = array(array('hardcoded' => true, 'code_position' => 0, 'position' => 0, 'value' => 'param value'));
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text, 'replacement text'));
		
		//Checking the translation:
		$this->assertEquals('translated test replacement text', $actual);
		
		//Cleaning:
		unset($expected['parameters']);
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
		// - String has 2 params. Param code positions are reversed.
		// 	-> the values should fill out in reverse order.
		
		//Creating the item:
		$expected['parameters'] = array(
					array('hardcoded' => true, 'code_position' => 0, 'position' => 1, 'value' => 'second value'),
					array('hardcoded' => true, 'code_position' => 1, 'position' => 0, 'value' => 'first value')
				);
		$expected['translation'] = 'testing %s and %s';
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text, 'second replacement', 'first replacement'));
		
		//Checking the translation:
		$this->assertEquals('testing first replacement and second replacement', $actual);
		
		//Cleaning:
		unset($expected['parameters']);
		$expected['translation'] = 'translated test %s';
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
				
		// - String has 1 param. Param at position 1 has a value.
		// 	-> value at position 0 hasn't been modified so it'll still work.
		
		//Creating the item:
		$expected['parameters'] = array(array('hardcoded' => false, 'code_position' => 0, 'position' => 1, 'value' => 'param value'));
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text));
		
		//Checking the translation:
		$this->assertEquals('translated test param value', $actual);
		
		//Cleaning:
		unset($expected['parameters']);
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
		// - String has 1 param. Param at position 0 has a value.
		// 	-> value should be filled.
		
		//Creating the item:
		$expected['parameters'] = array(array('hardcoded' => false, 'code_position' => 0, 'position' => 0, 'value' => 'param value'));
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text));
		
		//Checking the translation:
		$this->assertEquals('translated test param value', $actual);
		
		//Cleaning:
		unset($expected['parameters']);
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();		
		
		// - String has 1 param. Param at position 0 is an empty string.
		// 	-> value should be empty.
		
		//Creating the item:
		$expected['parameters'] = array(array('hardcoded' => false, 'code_position' => 0, 'position' => 0, 'value' => ''));
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text));
		
		//Checking the translation:
		$this->assertEquals('translated test ', $actual);
		
		//Cleaning:
		unset($expected['parameters']);
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
		// - String has 2 params. Only param at position 1 set, hardcoded to code position 0.
		// 	-> both should be filled with the value from code position 0, because only the second was modified.
		
		//Creating the item:
		$expected['parameters'] = array(
					array('hardcoded' => true, 'code_position' => 0, 'position' => 1, 'value' => 'param value')
				);
		$expected['translation'] = 'testing %s and %s';
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text, 'first replacement', 'second replacement'));
		
		//Checking the translation:
		$this->assertEquals('testing first replacement and first replacement', $actual);
		
		//Cleaning:
		unset($expected['parameters']);
		$expected['translation'] = 'translated test %s';
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
		// - String has 2 params. Only param at position 0 set, hardcoded to code position 1.
		// 	-> same as the last scenario just in reverse.
		
		//Creating the item:
		$expected['parameters'] = array(
				array('hardcoded' => true, 'code_position' => 1, 'position' => 0, 'value' => 'param value')
		);
		$expected['translation'] = 'testing %s and %s';
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text, 'first replacement', 'second replacement'));
		
		//Checking the translation:
		$this->assertEquals('testing second replacement and second replacement', $actual);
		
		//Cleaning:
		unset($expected['parameters']);
		$expected['translation'] = 'translated test %s';
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
		// - String has 2 params. Only param at position 0 set, has a value.
		// 	-> First param will be set from the attribute, the second from the hardcoded values.
		
		//Creating the item:
		$expected['parameters'] = array(
				array('hardcoded' => false, 'code_position' => 0, 'position' => 0, 'value' => 'param value')
		);
		$expected['translation'] = 'testing %s and %s';
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text, 'first replacement', 'second replacement'));
		
		//Checking the translation:
		$this->assertEquals('testing param value and second replacement', $actual);
		
		//Cleaning:
		unset($expected['parameters']);
		$expected['translation'] = 'translated test %s';
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();		
		
		// - String has 2 params. Only param at position 1 set, has a value.
		// 	-> First param will be set from the hardcoded values, the second from the attributes.
		
		//Creating the item:
		$expected['parameters'] = array(
				array('hardcoded' => false, 'code_position' => 0, 'position' => 1, 'value' => 'param value')
		);
		$expected['translation'] = 'testing %s and %s';
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text, 'first replacement', 'second replacement'));
		
		//Checking the translation:
		$this->assertEquals('testing first replacement and param value', $actual);
		
		//Cleaning:
		unset($expected['parameters']);
		$expected['translation'] = 'translated test %s';
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();		
		
		// - String has 1 param. 2 params set with values, at positions 0 and 1.
		// 	-> only the value from position 0 should appear.
		
		//Creating the item:
		$expected['parameters'] = array(
				array('hardcoded' => false, 'code_position' => 0, 'position' => 0, 'value' => 'first value'),
				array('hardcoded' => false, 'code_position' => 0, 'position' => 1, 'value' => 'second value')
		);
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text, 'first replacement', 'second replacement'));
		
		//Checking the translation:
		$this->assertEquals('translated test first value', $actual);
		
		//Cleaning:
		unset($expected['parameters']);
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
		// - Value is a regular string.
		// 	-> value should appear as is.
		
		//Creating the item:
		$expected['parameters'] = array(array('hardcoded' => false, 'code_position' => 0, 'position' => 0, 'value' => 'param value'));
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text, 'replacement text'));
		
		//Checking the translation:
		$this->assertEquals('translated test param value', $actual);
		
		//Cleaning:
		unset($expected['parameters']);
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
		// - Value has a parameter.
		// 	-> parameter should appear as is.
		
		//Creating the item:
		$expected['parameters'] = array(array('hardcoded' => false, 'code_position' => 0, 'position' => 0, 'value' => 'param %s'));
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text, 'replacement text'));
		
		//Checking the translation:
		$this->assertEquals('translated test param %s', $actual);
		
		//Cleaning:
		unset($expected['parameters']);
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
		// - Value contains a custom variable and plain text.
		// 	-> custom variable should be parsed, plain text should appear as is.
		
		//Creating the item:
		$expected['parameters'] = array(array('hardcoded' => false, 'code_position' => 0, 'position' => 0, 'value' => 'param {{customVar code=test}}'));
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text, 'replacement text'));
		
		//Checking the translation:
		$this->assertEquals('translated test param testing', $actual);
		
		//Cleaning:
		unset($expected['parameters']);
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
		// - Value contains 2 custom variables with text in between and around.
		// 	-> custom variables should parse and the plain text should appear as is.
		
		//Creating the item:
		$expected['parameters'] = array(array('hardcoded' => false, 'code_position' => 0, 'position' => 0, 'value' => 'param {{customVar code=test}} and {{customVar code=test2}} more'));
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text, 'replacement text'));
		
		//Checking the translation:
		$this->assertEquals('translated test param testing and testing some more', $actual);
		
		//Cleaning:
		unset($expected['parameters']);
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
		// - String has 1 param. The param set has the wrong type.
		// 	-> the value should not fill from the attributes
		
		//Creating the item:
		$expected['parameters'] = array(array('hardcoded' => false, 'code_position' => 0, 'position' => 0, 'value' => 'param value'));
		$expected['translation'] = 'translated test %d';
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text, 'replacement text'));
		
		//Checking the translation:
		$this->assertEquals('translated test 0', $actual);
		
		//Cleaning:
		unset($expected['parameters']);
		$expected['translation'] = 'translated test %s';
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();

		// - String has quotes or double-quotes.
		// 	-> It should properly identify the string and translate it.
		
		//Creating the item:
		$expected['string'] = "don't";
		$expected['translation'] = "do";
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$text = new Mage_Core_Model_Translate_Expr($expected['string'], $expected['module']);
		$actual = $this->model->translate(array($text));
		
		//Checking the translation:
		$this->assertEquals('do', $actual);
		
		//Cleaning:
		$expected['string'] = 'test %s';
		$expected['translation'] = 'translated test %s';
		$text = new Mage_Core_Model_Translate_Expr($expected['string'], $expected['module']);
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
		// - String has backdashes.
		// 	-> It should properly identify the string and translate it.
		
		//Creating the item:
		$expected['string'] = "do\\n\\\t";
		$expected['translation'] = "do";
		$string_id = $this->model->addEntry($expected);
		$this->model->init('frontend');
		
		//Translating:
		$text = new Mage_Core_Model_Translate_Expr($expected['string'], $expected['module']);
		$actual = $this->model->translate(array($text));
		
		//Checking the translation:
		$this->assertEquals('do', $actual);
		
		//Cleaning:
		$expected['string'] = 'test %s';
		$expected['translation'] = 'translated test %s';
		$text = new Mage_Core_Model_Translate_Expr($expected['string'], $expected['module']);
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
		// - Custom variable exists but is in a different store than the one set.
		// 	-> the custom variable should return the default value.
		
		//Creating the item:
		$expected['parameters'] = array(array('hardcoded' => false, 'code_position' => 0, 'position' => 0, 'value' => 'param {{customVar code=test}}'));
		$string_id = $this->model->addEntry($expected);
		Mage::app()->setCurrentStore($stores[2]);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text, 'replacement text'));
		
		//Checking the translation:
		$this->assertEquals('translated test param testing', $actual);
		
		//Cleaning:
		unset($expected['parameters']);
		Mage::app()->setCurrentStore($stores[0]);
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();

		// - Custom variable exists and set to the right store view.
		// 	-> the value should be filled.
		
		//Creating the item:
		$expected['parameters'] = array(array('hardcoded' => false, 'code_position' => 0, 'position' => 0, 'value' => 'param {{customVar code=test2}}'));
		$string_id = $this->model->addEntry($expected);
		Mage::app()->setCurrentStore($stores[2]);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text, 'replacement text'));
		
		//Checking the translation:
		$this->assertEquals('translated test param examen', $actual);
		
		//Cleaning:
		unset($expected['parameters']);
		Mage::app()->setCurrentStore($stores[0]);
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
		// - Custom variable doesn't exist.
		// 	-> the value should be empty.
		
		//Creating the item:
		$expected['parameters'] = array(array('hardcoded' => false, 'code_position' => 0, 'position' => 0, 'value' => 'param {{customVar code=test3}}'));
		$string_id = $this->model->addEntry($expected);
		Mage::app()->setCurrentStore($stores[2]);
		$this->model->init('frontend');
		
		//Translating:
		$actual = $this->model->translate(array($text, 'replacement text'));
		
		//Checking the translation:
		$this->assertEquals('translated test param ', $actual);
		
		//Cleaning:
		unset($expected['parameters']);
		Mage::app()->setCurrentStore($stores[0]);
		Mage::getModel('translator/string')->load($string_id)->delete();
		$this->resetModel();
		
	}
	
	/**
	 * 
	 * - String contains module.
	 * 	-> should save for that module.
	 * - String doesn't contain module.
	 * 	-> should save with null in the module field (change that in the code).
	 * - Store id set to zero and locale set to something non-default.
	 * 	-> should save to the store id that matches the locale?
	 */
	public function testMigrateCodeDb()
	{
		//Make sure there aren't any items in store:
		$this->assertEquals(0, count(Mage::getModel('translator/string')->getCollection()->load()));
		
		$expected= array(
					'test' => array('store_id' => 0, 'translation' => 'translated test', 'locale' => 'en_US', 'module' => ''),
					'Mage_Test::test2' => array('store_id' => 0, 'translation' => 'translated test 2', 'locale' => 'en_US', 'module' => 'Mage_Test'),
					'test3' => array('store_id' => 0, 'translation' => 'examen', 'locale' => 'fr_FR', 'module' => ''),
					'test4' => array('store_id' => 2, 'translation' => 'examen', 'locale' => 'fr_FR', 'module' => '')
				);
		
		$core_string = new Mage_Core_Model_Translate_String();
		foreach ($expected as $key => $item) {
			$core_string->getResource()->saveTranslate($key, $item['translation'], $item['locale'], $item['store_id']);
		}
		
		//Setting values into how we're expecting them to be saved:
		$expected['test2'] = $expected['Mage_Test::test2'];
		unset($expected['Mage_Test::test2']);
		$expected['test3']['store_id'] = 2;
		
		//Make the core table transfer:
		$this->model->migrateCoreDb();
		
		//Check that it saved all the items correctly:
		$items = Mage::getModel('translator/translation')->getCollection()->load();
		$this->assertEquals(4, count($items));
		foreach ($items as $item) {
			$string_item = Mage::getModel('translator/string')->load($item->getStringId());
			$this->assertEquals($expected[$string_item->getString()]['store_id'], $item->getStoreId());
			$this->assertEquals($expected[$string_item->getString()]['translation'], $item->getTranslation());
			$this->assertEquals($expected[$string_item->getString()]['locale'], $item->getLocale());
			$this->assertEquals($expected[$string_item->getString()]['module'], $string_item->getModule());
			$string_item->delete();
		}
		
		//Make sure there aren't any extra items that were created:
		$this->assertEquals(0, count(Mage::getModel('translator/string')->getCollection()->load()));
		
		//Cleaning:
		$expected['Mage_Test::test2'] = $expected['test2'];
		unset($expected['test2']);
		foreach ($expected as $key => $item) {
			$core_string->getResource()->deleteTranslate($key, $item['locale']);
		}
	}
	
	public function tearDown()
	{

	}
}