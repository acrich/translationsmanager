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
		return $this->model->getCollection()->getLastItem()->getStringId();
	}
	
	/**
	 * 
	 * 
	 * Tests:
	 *  - All attributes set, an item matches and it has the same string_id.
	 *  	-> It should update the item with the string_id with all the new attribute values.
	 *  - An item matches but has a different id than the one that's set.
	 *  	-> It should delete the item with string_id and set the new values into the matching one.
	 *  - No extra attributes set, and no item matches.
	 *  	-> It should update the item with string_id (the unmatched item) with the null values.
	 *  - No string_id set and no item matches the other attributes.
	 *  	-> It should create a new item with those attributes.
	 *  - No string_id set but an item exists that matches the other attributes.
	 *  	-> It should update the matching item with the new values.
	 *  - Both a string attribute that contains a module code, and a module attribute.
	 *  	-> It should override the module attribute with the code from within the string attribute.
	 */
	public function testUpdateItem()
	{
		
		//Initial setup:
		$expected = array(
				'string' => 'test',
				'module' => 'Wheelbarrow_Test'
		);
		$expected['string_id'] = $this->createItem($expected);
		
		// - All attributes set, an item matches and it has the same string_id
		
		//Prepare:
		$expected['parameters'] = array(
				array(
						'hardcoded' => true,
						'code_position' => 0,
						'position' => 0,
						'value' => ''
				));
		$expected['status'] = false;
		
		//Update:
		$this->model->updateItem($expected);
		$actual = $this->model->load($expected['string_id']);
		
		//Check all attributes:
		$this->assertEquals($expected['parameters'], unserialize($actual->getParameters()));
		$this->assertEquals(0, $actual->getStatus());
		$this->assertEquals($expected['string'], $actual->getString());
		$this->assertEquals($expected['module'], $actual->getModule());
		
		//Cleanup:
		$actual->delete();
		unset($expected['parameters']);
		unset($expected['status']);
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
		
		//Recreate the item:
		$expected['string_id'] = $this->createItem($expected);
		
		// - An item matches but has a different id than the one that's set
		
		//Create a second item:
		$id = $expected['string_id'];
		$expected['string_id'] = $this->model->setData(array('string' => 'temp', 'module' => 'Wheelbarrow_Temp'))->save()->getStringId();
		
		//Do the update:
		$this->model->updateItem($expected);
		
		//Check that it only kept the first item:
		$this->assertEquals(1, count($this->model->getCollection()->load()));
		
		//Check the attributes:
		$actual = $this->model->load($id);
		$this->assertEquals($expected['string'], $actual->getString());
		$this->assertEquals($expected['module'], $actual->getModule());
		
		//Cleanup:
		$actual->delete();
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
		
		//Recreate the item:
		$expected['string_id'] = $this->createItem($expected);
		
		// - No extra attributes set, and no item matches

		//Update:
		$expected['module'] = 'Wheelbarrow_Temp';
		$this->model->updateItem($expected);
		$actual = $this->model->load($expected['string_id']);
		
		//Check all attributes:
		$this->assertEquals(array(), unserialize($actual->getParameters()));
		$this->assertEquals(1, $actual->getStatus());
		$this->assertEquals($expected['string'], $actual->getString());
		$this->assertEquals($expected['module'], $actual->getModule());
		
		//Cleanup:
		$actual->delete();
		$expected['module'] = 'Wheelbarrow_Test';
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
		
		//Recreate the item:
		$expected['string_id'] = $this->createItem($expected);
		
		// - No string_id set and no item matches the other attributes
		
		//Remove the matching item and string_id:
		$this->model->load($expected['string_id'])->delete();
		unset($expected['string_id']);
		
		//Do the update:
		$this->model->updateItem($expected);
		
		//Check that an item has been created:
		$this->assertEquals(1, count($this->model->getCollection()->load()));
		
		//Check all attributes:
		$actual = $this->model->getCollection()->getLastItem();
		$this->assertEquals(array(), unserialize($actual->getParameters()));
		$this->assertEquals(1, $actual->getStatus());
		$this->assertEquals($expected['string'], $actual->getString());
		$this->assertEquals($expected['module'], $actual->getModule());
		
		//Cleanup:
		$actual->delete();
		
		//Recreate the item:
		$expected['string_id'] = $this->createItem($expected);
		
		// - No string_id set but an item exists that matches the other attributes
		
		//Do the update:
		$id = $expected['string_id'];
		unset($expected['string_id']);
		$expected['status'] = false;
		$this->model->updateItem($expected);

		//Check the attributes:
		$actual = $this->model->load($id);
		$this->assertEquals(0, $actual->getStatus());
		
		//Cleanup:
		$actual->delete();
		unset($expected['status']);

		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
		
		//Recreate the item:
		$expected['string_id'] = $this->createItem($expected);

		// - Both a string attribute that contains a module code, and a module attribute
		
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
		
	}
	
	public function testSetItem()
	{
		
		//Initial setup:
		$expected = array(
				'string' => 'test',
				'module' => 'Wheelbarrow_Test'
		);
		
		// - string_id doesn't exist.
		
		//Set the item:
		$this->model->setItem($expected);
		
		//Make sure it exists:
		$this->assertEquals(1, count($this->model->getCollection()->load()));
		
		//Check the attributes:
		$actual = $this->model->getCollection()->getLastItem();
		$this->assertEquals($expected['string'], $actual->getString());
		$this->assertEquals($expected['module'], $actual->getModule());
		
		//Cleanup:
		$actual->delete();
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
		
		// - string_id exists with a valid value.
		
		//Set the item:
		$this->model->setItem($expected);
		$expected['string_id'] = $this->model->getCollection()->getLastItem()->getStringId();
		
		//Reset it:
		$expected['string'] = 'temp';
		$this->model->setItem($expected);
		
		//Make sure there's just one item:
		$this->assertEquals(1, count($this->model->getCollection()->load()));
		
		//Check the update:
		$actual = $this->model->load($expected['string_id']);
		$this->assertEquals($expected['string'], $actual->getString());
		$this->assertEquals($expected['module'], $actual->getModule());
		
		//Cleanup:
		$actual->delete();
		$expected['string'] = 'test';
		unset($expected['string_id']);
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
		
		// - string_id exists with a value of zero.
		
		//Set the item:
		$expected['string_id'] = 0;
		$this->model->setItem($expected);
		
		//Make sure it exists:
		$this->assertEquals(1, count($this->model->getCollection()->load()));
		
		//Check the attributes:
		$actual = $this->model->getCollection()->getLastItem();
		$this->assertEquals($expected['string'], $actual->getString());
		$this->assertEquals($expected['module'], $actual->getModule());
		
		//Cleanup:
		$actual->delete();
		unset($expected['string_id']);
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
		
		// - string_id is null.
		
		//Set the item:
		$expected['string_id'] = null;
		$this->model->setItem($expected);
		
		//Make sure it exists:
		$this->assertEquals(1, count($this->model->getCollection()->load()));
		
		//Check the attributes:
		$actual = $this->model->getCollection()->getLastItem();
		$this->assertEquals($expected['string'], $actual->getString());
		$this->assertEquals($expected['module'], $actual->getModule());
		
		//Cleanup:
		$actual->delete();
		unset($expected['string_id']);
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
	}

	public function tearDown()
	{
		
	}
}