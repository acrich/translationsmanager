<?php
require_once 'PHPUnit/Autoload.php';
require_once '/var/www/translator/app/Mage.php';
 
class Wheelbarrow_Translator_Model_String_Test extends PHPUnit_Framework_TestCase {

	public function log($message)
	{
		$message = file_get_contents('shay.log')."\n"."\n".'Message: '.json_encode($message);
		file_put_contents('shay.log', $message);
	}
	
	public function setUp()
	{
		Mage::app('default');
		$this->model = Mage::getModel('translator/string');
	}
	
	public function testCreateItem()
	{
		//Initial setup:
		$expected = array(
					'string' => 'test',
					'module' => 'Wheelbarrow_Test'
				);
		
		// - Same item exists already
		
		//Create the first item:
		$this->model->createItem($expected);
		$id = $this->model->getCollection()->getLastItem()->getStringId();
		
		//Create it again:
		$this->model->createItem($expected);
		$second_item = $this->model->getCollection()->getLastItem();
		
		//Check that there aren't two items:
		$this->assertEquals(1, count($this->model->getCollection()->load()));
		
		//Cleanup:
		$second_item->delete();
		$expected['string'] = 'test';
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
		
		// - String contains module, no module attribute in item
		
		//Create the item:
		unset($expected['module']);
		$expected['string'] = 'Wheelbarrow_Temp::temp';
		$this->model->createItem($expected);
		$item = $this->model->getCollection()->getLastItem();
		
		//Check that it exists:
		$this->assertGreaterThan(0, $item->getStringId());
		
		//Check the values:
		$this->assertEquals('temp', $item->getString());
		$this->assertEquals('Wheelbarrow_Temp', $item->getModule());
		
		//Cleanup:
		$item->delete();
		$expected['string'] = 'test';
		$expected['module'] = 'Wheelbarrow_Test';
			
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
	
		// - String contains module, and there is a module attribute
		
		//Create the item:
		$expected['string'] = 'Wheelbarrow_Temp::temp';
		$this->model->createItem($expected);
		$item = $this->model->getCollection()->getLastItem();
		
		//Check that it exists:
		$this->assertGreaterThan(0, $item->getStringId());
		
		//Check the values:
		$this->assertEquals('temp', $item->getString());
		$this->assertEquals('Wheelbarrow_Temp', $item->getModule());
		
		//Cleanup:
		$item->delete();
		$expected['string'] = 'test';
		$expected['module'] = 'Wheelbarrow_Test';
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
		
		// - String doesn't contain the module, and there isn't a module attribute
		
		//Create the item:
		unset($expected['module']);
		$this->model->createItem($expected);
		$item = $this->model->getCollection()->getLastItem();
		
		//Check that it exists:
		$this->assertGreaterThan(0, $item->getStringId());
		
		//Check the values:
		$this->assertEquals($expected['string'], $item->getString());
		$this->assertNull($item->getModule());
		
		//Cleanup:
		$item->delete();
		$expected['module'] = 'Wheelbarrow_Test';
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
		
		// - Parameters set
		
		//Create the item:
		$expected['parameters'] = array(
									array(
										'hardcoded' => true, 
										'code_position' => 20, 
										'position' => 10, 
										'value' => 'temp'
								  ));
		$this->model->createItem($expected);
		$item = $this->model->getCollection()->getLastItem();
		
		//Check that it exists:
		$this->assertGreaterThan(0, $item->getStringId());
		
		//Check the values:
		$this->assertEquals($expected['parameters'], unserialize($item->getParameters()));
		
		//Cleanup:
		$item->delete();
		unset($expected['parameters']);
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
		
		// - Parameters not set, and there are parameters in the string
		
		//Create the item:
		$expected['string'] = 'test %s';
		$this->model->createItem($expected);
		$item = $this->model->getCollection()->getLastItem();
		
		//Check that it exists:
		$this->assertGreaterThan(0, $item->getStringId());
		
		//Check the values:
		$this->assertEquals($expected['string'], $item->getString());
		$parameters = array(
				array(
						'hardcoded' => true,
						'code_position' => 0,
						'position' => 0,
						'value' => ''
				));
		$this->assertEquals($parameters, unserialize($item->getParameters()));
		
		//Cleanup:
		$item->delete();
		$expected['string'] = 'test';
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->model->getCollection()->load()));		
		
		// - Status set to false
		
		//Create the item:
		$expected['status'] = false;
		$this->model->createItem($expected);
		$item = $this->model->getCollection()->getLastItem();
		
		//Check that it exists:
		$this->assertGreaterThan(0, $item->getStringId());
		
		//Check the values:
		$this->assertEquals(0, $item->getStatus());
		
		//Cleanup:
		$item->delete();
		unset($expected['status']);
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
		
		// - Status not set
		
		//Create the item:
		$this->model->createItem($expected);
		$item = $this->model->getCollection()->getLastItem();
		
		//Check that it exists:
		$this->assertGreaterThan(0, $item->getStringId());
		
		//Check the values:
		$this->assertEquals(1, $item->getStatus());
		
		//Cleanup:
		$item->delete();
		unset($expected['status']);
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
	}
	
	public function createItem($expected)
	{
		$this->model->createItem($expected);
		$expected['string_id'] = $this->model->getCollection()->getLastItem()->getStringId();
	}
	
	public function testUpdateItem()
	{
		
		//Initial setup:
		$expected = array(
				'string' => 'test',
				'module' => 'Wheelbarrow_Test'
		);
		$expected['string_id'] = $this->createItem($expected);
		
		// - With parameters, status and module
		
		//Prepare:
		$expected['parameters'] = array(
				array(
						'hardcoded' => true,
						'code_position' => 0,
						'position' => 0,
						'value' => ''
				));
		$expected['status'] = false;
		$expected['module'] = 'Wheelbarrow_Temp';
		
		//Update:
		$this->model->updateItem($expected);
		$actual = $this->model->load($expected['string_id']);
		
		//Check attributes:
		$this->assertEquals($expected['parameters'], unserialize($actual->getParameters()));
		$this->assertEquals(0, $actual->getStatus());
		$this->assertEquals($expected['module'], $actual->getModule());
		
		//Cleanup:
		$actual->delete();
		unset($expected['parameters']);
		unset($expected['status']);
		$expected['module'] = 'Wheelbarrow_Test';
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
		
		//Recreate the item:
		$expected['string_id'] = $this->createItem($expected);
		
		// - Without parameters, status and module
		
		//Update:
		unset($expected['module']);
		$this->model->updateItem($expected);
		$actual = $this->model->load($expected['string_id']);
		
		//Check attributes:
		$this->assertEquals(array(), unserialize($actual->getParameters()));
		$this->assertEquals(1, $actual->getStatus());
		$this->assertNull($actual->getModule());
		
		//Cleanup:
		$actual->delete();
		$expected['module'] = 'Wheelbarrow_Test';
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
		
		//Recreate the item:
		$expected['string_id'] = $this->createItem($expected);
		
		// - With a string that contains the module, and a module attribute
		
		//Update:
		$expected['string'] = 'Wheelbarrow_Temp::temp';
		$this->model->updateItem($expected);
		$actual = $this->model->load($expected['string_id']);
		
		//Check attributes:
		$this->assertEquals('temp', $actual->getString());
		$this->assertEquals('Wheelbarrow_Temp', $actual->getModule());
		
		//Cleanup:
		$actual->delete();
		$expected['string'] = 'test';
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
		
		//Recreate the item:
		$expected['string_id'] = $this->createItem($expected);
		
		// - Without string_id and with no matching item	
		
		// - With string_id and without a matching item
		
		// - With string_id and with a matching item that has a different id
		
		// - With string_id and with a matching item that has the same id
	}

	public function tearDown()
	{
		
	}
}