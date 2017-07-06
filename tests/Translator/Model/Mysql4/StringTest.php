<?php
require_once 'PHPUnit/Autoload.php';
require_once '/var/www/translator/app/Mage.php';

class Wheelbarrow_Translator_Model_Mysql4_String_Test extends PHPUnit_Framework_TestCase {
	
	public function log($message)
	{
		$message = file_get_contents('shay.log')."\n"."\n".'Message: '.json_encode($message);
		file_put_contents('shay.log', $message);
	}
	
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
	 * 	-> Only the last one (with the lowest store_id) is returned.
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
		//Set the current store view:
		$stores = Mage::app()->getStores(true);
		Mage::app()->setCurrentStore($stores[1]);
		
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
					)
				);
		
		$ids = array();
		foreach($data as $item) {
			$ids[] = Mage::getModel('translator/translate')->addEntry($item);
		}
		
		//- The locale parameter is passed fr_FR.
		$results = $this->resource->getTranslationArrayByModule('fr_FR', 'frontend');
		
		//Check that no items were found:
		$this->assertEquals(0, count($results));
		
		//- The locale parameter isn't passed.
		$results = $this->resource->getTranslationArrayByModule(null, 'frontend');
		
		//Check that it finds both items:
		$this->assertEquals(2, count($results));
		$this->assertEquals(array($data[0]['string'] => $data[0]['translation']), $results[$data[0]['module']]);
		$this->assertEquals(array($data[1]['string'] => $data[1]['translation']), $results[$data[1]['module']]);
		
		//- Two items match in every way, except the store view on one is the default one.
		
		//Create the new item:
		$data[1]['store_id'] = 1;
		$data[1]['translation']= 'a different translation';
		$ids[] = Mage::getModel('translator/translate')->addEntry($data[1]);
		
		//Check that it only finds the less specific of the items with the same string_id:
		$results = $this->resource->getTranslationArrayByModule(null, 'frontend');
		$this->assertEquals(2, count($results));
		$data[1]['translation']= 'translated temp';
		$this->assertEquals(array($data[1]['string'] => $data[1]['translation']), $results[$data[1]['module']]);
	
		//- Out of the two items, only one matches the passed area code.
		
		//Modify the frontend area field on the first item to false:
		$items = Mage::getModel('translator/translation')->getCollection()
			->addFieldToFilter('string_id', $ids[0])
			->load();
		foreach ($items as $item) {
			$item->setFrontend(false)->save();
		}
		
		//Check that the function only returns the other item:
		$results = $this->resource->getTranslationArrayByModule(null, 'frontend');
		$this->assertEquals(1, count($results));
		$this->assertEquals(array($data[1]['string'] => $data[1]['translation']), $results[$data[1]['module']]);
		
		//- Out of the two items, only one is enabled.
		
		//Reset the item's frontend field:
		$items = Mage::getModel('translator/translation')->getCollection()
			->addFieldToFilter('string_id', $ids[0])
			->load();
		foreach ($items as $item) {
			$item->setFrontend(true)->save();
		}
		
		//Check that it finds both items:
		$results = $this->resource->getTranslationArrayByModule(null, 'frontend');
		$this->assertEquals(2, count($results));
		
		//Set the first item's status to false:
		Mage::getModel('translator/string')->load($ids[0])->setStatus(false)->save();
		
		//Check that it only gets the other item:
		$results = $this->resource->getTranslationArrayByModule(null, 'frontend');
		$this->assertEquals(1, count($results));
		$this->assertEquals(array($data[1]['string'] => $data[1]['translation']), $results[$data[1]['module']]);
		
		//- Item doesn't have a module set.
		
		//Set the second item's module to null:
		Mage::getModel('translator/string')->load($ids[1])->setModule(null)->save();
		
		//Check that it returns the item with the store id as a scope:
		$results = $this->resource->getTranslationArrayByModule(null, 'frontend');
		$this->assertEquals(1, count($results));
		$this->assertEquals(array($data[1]['string'] => $data[1]['translation']), $results[$data[1]['store_id']]);
		
		
		//Clean all the items:
		foreach ($ids as $id) {
			Mage::getModel('translator/string')->load($id)->delete();
		}
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
		//- No string set.
		
		//Check that there aren't any string items:
		$this->assertEquals(0, count(Mage::getModel('translator/string')->getCollection()->load()));
		
		//Get the item id:
		$expected = array('string' => 'test', 'module' => 'Wheelbarrow_Test');
		$id = $this->resource->getIdByParams($expected);
		
		//Make sure nothing was returned:
		$this->assertEquals('', $id);
		
		//- Module code is both inserted into the string and in its own attribute.
		
		//Create two items that diverge only in the module:
		$ids = array();
		$ids[0] = Mage::getModel('translator/string')->createItem($expected);
		$expected['module'] = 'Wheelbarrow_Temp';
		$ids[1] = Mage::getModel('translator/string')->createItem($expected);
		
		//Get the item id:
		$expected['module'] = 'Wheelbarrow_Test';
		$expected['string'] = 'Wheelbarrow_Temp::test';
		$id = $this->resource->getIdByParams($expected);
		
		//Check that it returned the second item:
		$this->assertEquals($ids[1], $id);
		
		//- String doesn't have module code, module is set
		
		//Get the item id:
		$expected['module'] = 'Wheelbarrow_Test';
		$expected['string'] = 'test';
		$id = $this->resource->getIdByParams($expected);
		
		//Check that it returned the first item:
		$this->assertEquals($ids[0], $id);
		
		//- String doesn't have module code, module is null
		
		//Check that it doesn't find an item with a null module field:
		$expected['module'] = null;
		$id = $this->resource->getIdByParams($expected);
		$this->assertEquals('', $id);
		
		//Modify the second item to a null module field:
		Mage::getModel('translator/string')->load($ids[1])->setModule(null)->save();
		
		//Check that it finds the modified item:
		$id = $this->resource->getIdByParams($expected);
		$this->assertEquals($ids[1], $id);
		
		//- String doesn't have module code, module isn't set
		
		//Remove the second item:
		Mage::getModel('translator/string')->load($ids[1])->delete();
		
		//Check that it finds an item without regard to the module:
		unset($expected['module']);
		$id = $this->resource->getIdByParams($expected);
		$this->assertEquals($ids[0], $id);
		
		//- String has parameter syntax.
		
		//Create another item:
		$expected['module'] = 'Wheelbarrow_Test';
		$expected['string'] = 'test%s';
		$ids[1] = Mage::getModel('translator/string')->createItem($expected);
		
		//Check that it returns the item with the parameter:
		$id = $this->resource->getIdByParams($expected);
		$this->assertEquals($ids[1], $id);
		
		//- No item exists with that string and module
		
		//Get the id:
		$expected['module'] = 'Wheelbarrow_Testing';
		$expected['string'] = 'testing';
		$id = $this->resource->getIdByParams($expected);
		
		//Check that it didn't find anything:
		$this->assertEquals('', $id);
		
		//- No item exists with that module. An item exists with that string and another module.
		
		//Get the id:
		$expected['module'] = 'Wheelbarrow_Testing';
		$expected['string'] = 'test';
		$id = $this->resource->getIdByParams($expected);
		
		//Check that it didn't find anything:
		$this->assertEquals('', $id);
		
		//- Two items exist.
		
		//Create a duplicate of the first item with a null module:
		Mage::getModel('translator/string')->load($ids[1])
			->setString('test')
			->setModule(null)
			->save();
		
		//Make sure two items exist:
		$this->assertEquals(2, count(Mage::getModel('translator/string')->getCollection()->load()));
		
		//Get the id:
		$expected['module'] = 'Wheelbarrow_Test';
		$id = $this->resource->getIdByParams($expected);
		
		//Check that it found one of the items:
		$this->assertThat($id,
				$this->logicalOr(
					$this->equalTo($ids[0]),
					$this->equalTo($ids[1])
				));
		
		
		//Clean all the items:
		foreach ($ids as $id) {
			Mage::getModel('translator/string')->load($id)->delete();
		}
	}
}
