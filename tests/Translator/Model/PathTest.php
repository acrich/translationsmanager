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
	 * - With file and offset attributes, two items match only the string and path, one with file and offset.
	 * 	-> the one without file and offset is updated and its id is returned. The other isn't modified. No new items are created.
	 * - With file and offset attributes, two items match only the string and path, none with file and offset.
	 * 	-> Both items are modified with the new values. No new items are created. One of their ids is returned.
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
		
	}

	/**
	 * 
	 * - Item exists that matches both path and string id.
	 * 	-> returns that item's id.
	 * - Item exists that matches only the path, with a different string_id
	 * 	-> returns false.
	 * 
	 */
	public function testGetMatchingId()
	{
		
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
		
	}
}