<?php
require_once 'PHPUnit/Autoload.php';
require_once '/var/www/translator/app/Mage.php';
 
class Wheelbarrow_Translator_Model_Path_Test extends PHPUnit_Framework_TestCase {
	
	public function log($message)
	{
		$message = file_get_contents('shay.log')."\n"."\n".'Message: '.json_encode($message);
		file_put_contents('shay.log', $message);
	}
	
	public function setUp()
	{
		Mage::app('default');
		$this->model = Mage::getModel('translator/path');
	}
	
	/**
	 * 
	 * - With file and offset attributes, an item matches all
	 * 	-> that item's id is returned. It isn't modified.
	 * - With file and offset attributes, an item matches only the string and path, has no file and offset attributes.
	 * 	-> That item is modified with the new values, and its id is returned. No new items are created.
	 * - With file and offset attributes, nothing matches.
	 * 	-> A new item is created with these values, its id is returned.
	 * - With file but without the offset attribute, no items match.
	 * 	-> A new item is created, its id is returned.
	 * - Without file and offset, no items match
	 * 	-> A new item is created, its id is returned.
	 * - Without file and offset, an item matches
	 * 	-> That item is updated with file and offset, and its id is returned.
	 * 
	 */
	public function testCreateItem()
	{
		$string_id = Mage::getModel('translator/string')->createItem(array('string' => 'test', 'module' => 'Wheelbarrow_Test'));
		$expected = array(
					'string_id' => $string_id,
					'path' => 'some path',
					'file' => 'some_file.php',
					'offset' => 10
				);
		
		// - With file and offset attributes, an item matches all

		//Create the matching item:
		$expected_id = $this->model->createItem($expected);
		
		//Resave the path item:
		$actual_id = $this->model->createItem($expected);
		
		//Check that the item wasn't modified:
		$this->assertEquals($expected_id, $actual_id);
		$item = $this->model->load($actual_id);
		$this->assertEquals($expected['string_id'], $item->getStringId());
		$this->assertEquals($expected['path'], $item->getPath());
		$this->assertEquals($expected['file'], $item->getFile());
		$this->assertEquals($expected['offset'], $item->getOffset());
		
		//Remove the item:
		$item->delete();
		
		//Check that there aren't any new items in store:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
		
		// - With file and offset attributes, an item matches only the string and path, has no file and offset attributes.
		
		//Create the matching item:
		unset($expected['file']);
		unset($expected['offset']);
		$expected_id = $this->model->createItem($expected);
		
		//Resave the path item:
		$expected['file'] = 'some_file.php';
		$expected['offset'] = 10;
		$actual_id = $this->model->createItem($expected);
		
		//Check that the item was modified correctly:
		$this->assertEquals($expected_id, $actual_id);
		$item = $this->model->load($actual_id);
		$this->assertEquals($expected['string_id'], $item->getStringId());
		$this->assertEquals($expected['path'], $item->getPath());
		$this->assertEquals($expected['file'], $item->getFile());
		$this->assertEquals($expected['offset'], $item->getOffset());
		
		//Remove the item:
		$item->delete();
		
		//Check that there aren't any new items in store:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
		
		// - With file and offset attributes, nothing matches.
		
		//Create the unmatching item:
		$expected['path'] = 'some other path';
		$expected_id = $this->model->createItem($expected);
		
		//Resave the path item:
		$expected['path'] = 'some path';
		$actual_id = $this->model->createItem($expected);
		
		//Check that a new item was created with the correct values:
		$this->assertNotEquals($expected_id, $actual_id);
		$item = $this->model->load($actual_id);
		$this->assertEquals($expected['string_id'], $item->getStringId());
		$this->assertEquals($expected['path'], $item->getPath());
		$this->assertEquals($expected['file'], $item->getFile());
		$this->assertEquals($expected['offset'], $item->getOffset());
		
		//Remove the item:
		$item->delete();
		$this->model->load($expected_id)->delete();
		
		//Check that there aren't any new items in store:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
		
		// - With file but without the offset attribute, no items match.
		
		//Create the unmatching item:
		$expected['path'] = 'some other path';
		$expected_id = $this->model->createItem($expected);
		
		//Resave the path item:
		unset($expected['offset']);
		$expected['path'] = 'some path';
		$actual_id = $this->model->createItem($expected);
		
		//Check that a new item was created with the correct values:
		$this->assertNotEquals($expected_id, $actual_id);
		$item = $this->model->load($actual_id);
		$this->assertEquals($expected['string_id'], $item->getStringId());
		$this->assertEquals($expected['path'], $item->getPath());
		$this->assertNull($item->getFile());
		$this->assertNull($item->getOffset());
		
		//Remove the item:
		$item->delete();
		$this->model->load($expected_id)->delete();
		
		//Check that there aren't any new items in store:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
		
		// - Without file and offset, no items match
		
		//Create the unmatching item:
		$expected['path'] = 'some other path';
		$expected_id = $this->model->createItem($expected);
		
		//Resave the path item:
		unset($expected['file']);
		unset($expected['offset']);
		$expected['path'] = 'some path';
		$actual_id = $this->model->createItem($expected);
		
		//Check that a new item was created with the correct values:
		$this->assertNotEquals($expected_id, $actual_id);
		$item = $this->model->load($actual_id);
		$expected['path'] = 'some path';
		$this->assertEquals($expected['string_id'], $item->getStringId());
		$this->assertEquals($expected['path'], $item->getPath());
		$this->assertNull($item->getFile());
		$this->assertNull($item->getOffset());
		
		//Remove the item:
		$item->delete();
		$this->model->load($expected_id)->delete();
		
		//Check that there aren't any new items in store:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
		
		// - Without file and offset, an item matches that has file and offset
		
		//Create the matching item:
		$expected_id = $this->model->createItem($expected);
		
		//Resave the path item:
		unset($expected['file']);
		unset($expected['offset']);
		$actual_id = $this->model->createItem($expected);
		
		//Check that a new item was created with the correct values:
		$this->assertEquals($expected_id, $actual_id);
		$item = $this->model->load($actual_id);
		$this->assertEquals($expected['string_id'], $item->getStringId());
		$this->assertEquals($expected['path'], $item->getPath());
		$this->assertNull($item->getFile());
		$this->assertNull($item->getOffset());
		
		//Remove the item:
		$item->delete();
		
		//Check that there aren't any new items in store:
		$this->assertEquals(0, count($this->model->getCollection()->load()));
		
		// - Without file and offset, an item matches that does not have file and offset
		
		//Create the matching item:
		unset($expected['file']);
		unset($expected['offset']);
		$expected_id = $this->model->createItem($expected);
		
		//Resave the path item:
		$actual_id = $this->model->createItem($expected);
		
		//Check that a new item was created with the correct values:
		$this->assertEquals($expected_id, $actual_id);
		$item = $this->model->load($actual_id);
		$this->assertEquals($expected['string_id'], $item->getStringId());
		$this->assertEquals($expected['path'], $item->getPath());
		$this->assertNull($item->getFile());
		$this->assertNull($item->getOffset());
		
		//Remove the item:
		$item->delete();
		
		//Check that there aren't any new items in store:
		$this->assertEquals(0, count($this->model->getCollection()->load()));

		//Once everything is done, remove the string item:
		Mage::getModel('translator/string')->load($string_id)->delete();
	}

	/**
	 * 
	 * - Item exists that matches both path and string id.
	 * 	-> returns that item's id.
	 * - Item exists that matches only the string_id, with a different path
	 * 	-> returns false.
	 * 
	 */
	public function testGetMatchingId()
	{
		$string_id = Mage::getModel('translator/string')->createItem(array('string' => 'test', 'module' => 'Wheelbarrow_Test'));
		
		//- Item exists that matches both path and string id.
		
		//Create the matching item:
		$expected = array('string_id' => $string_id, 'path' => 'some path');
		$id = $this->model->createItem($expected);
		
		//Get matches:
		$match = $this->model->getMatchingId($expected);
		
		//Check results:
		$this->assertEquals($id, $match);
		
		//Remove the item:
		$this->model->load($match)->delete();
		
		//- Item exists that matches only the string_id, with a different path
		
		$string_id = Mage::getModel('translator/string')->createItem(array('string' => 'test', 'module' => 'Wheelbarrow_Test'));
		
		//- Item exists that matches both path and string id.
		
		//Create the matching item:
		$expected = array('string_id' => $string_id, 'path' => 'some path');
		$this->model->createItem($expected);
		
		//Get matches:
		$expected['path'] = 'some other path';
		$match = $this->model->getMatchingId($expected);
		
		//Check results:
		$this->assertEquals(0, $match);
		
		//Once everything is done, remove the string item:
		Mage::getModel('translator/string')->load($string_id)->delete();
	}
	
	/**
	 * Going to setup:
	 * - Two items with the same path and string_id
	 * 	-> It'll only display the string_id once in the results.
	 * - One item with a different path.
	 * 	-> It won't display this one at all.
	 * - Another item with the same path but a differnt string_id
	 * 	-> This one gets displayed too.
	 */
	public function testGetStringIdsByPath()
	{
		//Setup the string items:
		$string_id = Mage::getModel('translator/string')->createItem(array('string' => 'test', 'module' => 'Wheelbarrow_Test'));
		$string_id2 = Mage::getModel('translator/string')->createItem(array('string' => 'temp', 'module' => 'Wheelbarrow_Temp'));
		$string_id3 = Mage::getModel('translator/string')->createItem(array('string' => 'another test', 'module' => 'Wheelbarrow_Test'));
		
		//Create all the path items:
		$expected = array('string_id' => $string_id, 'path' => 'some path', 'file' => 'some_file.txt', 'offset' => 10);
		$path_id1 = $this->model->createItem($expected);
		$expected['file'] = 'other_file.txt';
		$expected['offset'] = 20;
		$path_id2 = $this->model->createItem($expected);
		$expected['path'] = 'some other path';
		$expected['string_id'] = $string_id3;
		$path_id3 = $this->model->createItem($expected);
		$expected['path'] = 'some path';
		$expected['string_id'] = $string_id2;
		$path_id4 = $this->model->createItem($expected);
		
		//Search for matching items:
		$matches = $this->model->getStringIdsByPath($expected['path']);
		
		//Check the results:
		$this->assertEquals(2, count($matches));
		$this->assertEquals($string_id, $matches[$string_id]);
		$this->assertEquals($string_id2, $matches[$string_id2]);
		
		//Once everything is done, remove the items:
		$this->model->load($path_id1)->delete();
		$this->model->load($path_id2)->delete();
		$this->model->load($path_id3)->delete();
		$this->model->load($path_id4)->delete();
		
		Mage::getModel('translator/string')->load($string_id)->delete();
		Mage::getModel('translator/string')->load($string_id2)->delete();
		Mage::getModel('translator/string')->load($string_id3)->delete();
	}
	
	/**
	 * Going to setup:
	 * - Two items with that string_id
	 * 	-> they'll each get an array item.
	 * - One item with a different string_id
	 * 	-> This one won't be in the resulting array.
	 */
	public function testGetPathIdsByStringIds()
	{
		//Setup the string items:
		$string_id = Mage::getModel('translator/string')->createItem(array('string' => 'test', 'module' => 'Wheelbarrow_Test'));
		$string_id2 = Mage::getModel('translator/string')->createItem(array('string' => 'temp', 'module' => 'Wheelbarrow_Temp'));
		
		//Create all the path items:
		$expected = array('string_id' => $string_id, 'path' => 'some path', 'file' => 'some_file.txt', 'offset' => 10);
		$path_id1 = $this->model->createItem($expected);
		$expected['file'] = 'other_file.txt';
		$expected['offset'] = 20;
		$path_id2 = $this->model->createItem($expected);
		$expected['string_id'] = $string_id2;
		$path_id3 = $this->model->createItem($expected);
		
		//Search for matching items:
		$matches = $this->model->getPathIdsByStringId($string_id);
		
		//Check the results:
		$this->assertEquals(2, count($matches));
		$this->assertEquals($path_id1, $matches[$path_id1]);
		$this->assertEquals($path_id2, $matches[$path_id2]);
		
		//Once everything is done, remove the items:
		$this->model->load($path_id1)->delete();
		$this->model->load($path_id2)->delete();
		$this->model->load($path_id3)->delete();
		
		Mage::getModel('translator/string')->load($string_id)->delete();
		Mage::getModel('translator/string')->load($string_id2)->delete();
	}
}