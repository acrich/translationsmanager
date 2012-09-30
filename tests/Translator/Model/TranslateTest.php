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
						'translation' => 'test',
						'locale' => 'en_US',
						'store_id' => 1
					),
					1 => array(
						'string' => 'temp',
						'module' => 'Wheelbarrow_Temp',
						'translation' => 'temp',
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
		
		foreach($data as $item) {
			$this->model->addEntry($item);
		}
		
		//Init it again, so it'll populate the values:
		$this->model->init('frontend');
		
		//Test that it has the values:
		$actual = $this->model->getData();
		$this->assertEquals(3, count($actual));
		$this->assertEquals('Fuck off', $actual['Mage_Sales::Leave']);
		$this->assertEquals('test', $actual['Wheelbarrow_Test::test']);
		$this->assertEquals('temp', $actual['Wheelbarrow_Temp::temp']);
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
	 * - text is a string, and there's an item that fits with some module set that isn't the current scope.
	 * 	-> should translate.
	 * - text is a string, and there isn't an item that fits.
	 * 	-> should not translate.
	 * 
	 * - text has a matching item, but with the wrong store view.
	 * 	-> should not translate.
	 * - test has a matching item that's in the admin store view.
	 * 	-> should translate.
	 * 
	 * - There is an item, but its status is set to false, and there is parameter data to process.
	 * 	-> should translate without the parameter changes.
	 * - There is an item, and its status is set to true, and there is parameter data to process.
	 * 	-> should translate with the parameter changes.
	 * 
	 * - Parameters attribute is empty.
	 * 	-> should translate without any paramter changes.
	 * 
	 * - String has 1 param. Param at position 0 takes value from code at position 1
	 * 	-> value should be empty.
	 * - String has 1 param. Param at position 1 takes value from code at position 0
	 * 	-> value should be empty.
	 * - String has 1 param. Param at position 0 takes value from code at position 0, value set.
	 * 	-> value should be filled.
	 * - String has 2 params. Param code positions are reversed.
	 * 	-> the values should fill out in reverse order.
	 * - String has 1 param. Param at position 1 has a value.
	 * 	-> value should be empty.
	 * - String has 1 param. Param at position 0 has a value.
	 * 	-> value should be filled.
	 * - String has 1 param. Param at position 0 is an empty string.
	 * 	-> value should be empty.
	 * - String has 2 params. Only param at position 1 set, hardcoded to code position 0.
	 * 	-> value should be filled only for the second param, with the hardcoded value supplied, the other will be empty.
	 * - String has 2 params. Only param at position 0 set, hardcoded to code position 1.
	 * 	-> same as the last scenario just in reverse.
	 * - String has 2 params. Only param at position 0 set, has a value.
	 * 	-> only the first param should be set.
	 * - String has 2 params. Only param at position 1 set, has a value.
	 * 	-> only the second item should be set.
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
	 * 	-> the value should be empty
	 * - String has 2 params. The types are reversed.
	 * 	-> the values should be reversed.
	 * 
	 * - String has backdashes.
	 * 	-> ?
	 * - String has quotes and double-quotes.
	 * 	-> ?
	 * 
	 * - Custom variable exists but is in a different store than the one set.
	 * 	-> the value should be empty.
	 * - Custom variable exists but is set to the admin store view rather than the one set.
	 * 	-> the value should be empty
	 * - Custom variable exists and set to some store view, while we're in the admin store view.
	 * 	-> the value should be emtpy.
	 * - Custom variable exists and set to the right store view.
	 * 	-> the value should be filled.
	 * - Custom variable doesn't exist.
	 * 	-> the value should be empty.
	 * 
	 */
	public function testTranslate()
	{
		
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
		
	}
	
	public function tearDown()
	{

	}
}