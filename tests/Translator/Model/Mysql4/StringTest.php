<?php
require_once 'PHPUnit/Autoload.php';
require_once '/var/www/translator/app/Mage.php';

class Wheelbarrow_Translator_Model_Mysql4_String_Test extends PHPUnit_Framework_TestCase {
	
	public function setUp()
	{
		Mage::app('default');
		$this->resource = Mage::getModel('translator/string')->getResource();
	}
	
	/**
	 * 
	 * Going to setup:
	 * - Item set to admin store view, with a default locale.
	 * - Another item set to the currently set store view, with a default locale.
	 * Tests:
	 * - The locale parameter is passed fr_FR.
	 * 	-> It shouldn't find any results to display.
	 * - The locale parameter isn't passed.
	 * 	-> It should find both items that have the default locale.
	 * - Two items match in every way, except the store view on one is the default one.
	 * 	-> Only the last one (with the highest store_id) is returned.
	 * - Only an admin item and the function is passed another store view.
	 * 	-> It still displays that item.
	 * - No matching items.
	 * 	-> it returns nothing.
	 * - Out of the two items, only one matches the passed area code.
	 * 	-> Only that one is returned.
	 * - Out of the two items, only one is enabled. 
	 * 	-> Only that one is returned.
	 * - Item doesn't have a module set. 
	 * 	-> It should still return it, just with the store_id as a scope.
	 * 
	 */
	public function testGetTranslationArrayByModule()
	{
		
	}
	
	/**
	 * 
	 * - No string set.
	 * 	-> It should return false.
	 * - Module code is both inserted into the string and in its own attribute.
	 * 	-> It should use only the one from within the string.
	 * - String doesn't have module code, module is set
	 * 	-> It should look only for an item with that module set.
	 * - String doesn't have module code, module is null
	 * 	-> It should look only for an item that have null for module.
	 * - String doesn't have module code, module isn't set
	 * 	-> It should return any item, without regard to the value of the module field.
	 * - String has parameter syntax.
	 * 	-> It should not parse that as parameters.
	 * - No item exists with that string and module
	 * 	-> It should return null.
	 * - No item exists with that module. An item exists with that string and another module.
	 * 	-> It should return null.
	 * - Two items exist.
	 * 	-> It should return just the last one.
	 * 
	 */
	public function testGetItemByParams()
	{

	}
}
