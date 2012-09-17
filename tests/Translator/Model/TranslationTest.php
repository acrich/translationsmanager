<?php
require_once 'PHPUnit/Autoload.php';
require_once '/var/www/translator/app/Mage.php';
 
class Wheelbarrow_Translator_Model_Translation_Test extends PHPUnit_Framework_TestCase {
	 
	public function setUp()
	{
		Mage::app('default');
		$this->translation = Mage::getModel('translator/translation');
	}

	public function testCreateItem()
	{
		//Make sure there aren't any items.
		foreach ($this->translation->getCollection()->load() as $item) {
			$item->delete();
		}
		
		//Set defaults:
		$french_store = 2;
		$expected = array(
				'translation' => 'test',
				'store_id' => $french_store,
				'locale' => 'fr_FR',
				'areas' => array('frontend', 'adminhtml'),
				'string_id' => 1
		);
		
		//Test without a string id
		unset($expected['string_id']);
		$this->translation->createItem($expected);
		
		$id = $this->translation->getCollection()->getLastItem()->getTranslationId();
		
		$this->assertNull($id);
		
		//Resetting stuff:
		$expected['string_id'] = 1;

		//Test different combinations of store_id and locale:
		// - STORE_ID=TRUE, LOCALE=TRUE
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertNotEquals(false, $actual->getTranslationId());
		
		//Testing all attrubtes this time:
		$this->assertEquals($expected['string_id'], $actual->getStringId());
		$this->assertEquals($expected['store_id'], $actual->getStoreId());
		$this->assertEquals($expected['locale'], $actual->getLocale());
		$this->assertEquals($expected['translation'], $actual->getTranslation());
		foreach (array('frontend', 'adminhtml', 'install') as $area) {
			if (in_array($area, $expected['areas'])) {
				$this->assertEquals(1, $actual->getData($area));
			}
		}
		
		//Resetting stuff:
		$actual->delete();
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		// - STORE_ID=TRUE, LOCALE=FALSE
		unset($expected['locale']);
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $actual->getTranslationId());
		
		//Testing store_id and locale:
		$this->assertEquals($expected['store_id'], $actual->getStoreId());
		$this->assertEquals('fr_FR', $actual->getLocale());
		
		//Resetting stuff:
		$actual->delete();
		$expected['locale'] = 'fr_FR';
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		// - STORE_ID=TRUE, LOCALE=DEFAULT
		$expected['locale'] = 'en_US';
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $actual->getTranslationId());
		
		//Testing store_id and locale:
		$this->assertEquals($expected['store_id'], $actual->getStoreId());
		$this->assertEquals($expected['locale'], $actual->getLocale());
		
		//Resetting stuff:
		$actual->delete();
		$expected['locale'] = 'fr_FR';
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));

		// - STORE_ID=DEFAULT, LOCALE=FALSE
		$expected['store_id'] = 0;
		unset($expected['locale']);
		
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $actual->getTranslationId());
		
		//Testing store_id and locale:
		$this->assertEquals($expected['store_id'], $actual->getStoreId());
		$this->assertEquals('en_US', $actual->getLocale());

		//Resetting stuff:
		$actual->delete();
		$expected['store_id'] = $french_store;
		$expected['locale'] = 'fr_FR';
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		// - STORE_ID=DEFAULT, LOCALE=TRUE (expecting multiple items)
		$expected['store_id'] = 0;
		$expected['locale'] = 'ar_SA';
		
		$this->translation->createItem($expected);
		
		//Testing store_id and locale:
		$stores = array(3 => 3, 4 => 4);
		$items = $this->translation->getCollection()->load();
		$this->assertEquals(2, count($items));
		
		foreach($items as $item) {
			//We're testing that each item is for a different store 
			//	and that there aren't any more than those two.
			$this->assertContains($item->getStoreId(), $stores);
			unset($stores[$item->getStoreId()]);
			
			$this->assertEquals($expected['locale'], $item->getLocale());
			
			$item->delete();
		}
		
		//Resetting stuff (items were deleted from within the foreach loop):
		$expected['store_id'] = $french_store;
		$expected['locale'] = 'fr_FR';
		
		// - STORE_ID=FALSE, LOCALE=TRUE (expecting multiple items)
		unset($expected['store_id']);
		$expected['locale'] = 'ar_SA';
		
		$this->translation->createItem($expected);
		
		//Testing store_id and locale:
		$stores = array(3 => 3, 4 => 4);
		$items = $this->translation->getCollection()->load();
		$this->assertEquals(2, count($items));
		
		foreach($items as $item) {
			//We're testing that each item is for a different store 
			//	and that there aren't any more than those two.
			$this->assertContains($item->getStoreId(), $stores);
			unset($stores[$item->getStoreId()]);
			
			$this->assertEquals($expected['locale'], $item->getLocale());
			
			$item->delete();
		}
		
		//Resetting stuff (items were deleted from within the foreach loop):
		$expected['store_id'] = $french_store;
		$expected['locale'] = 'fr_FR';
		
		// - STORE_ID=FALSE, LOCALE=DEFAULT
		unset($expected['store_id']);
		$expected['locale'] = 'en_US';
		
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $actual->getTranslationId());
		
		//Testing store_id and locale:
		$this->assertEquals(0, $actual->getStoreId());
		$this->assertEquals($expected['locale'], $actual->getLocale());

		//Resetting stuff:
		$actual->delete();
		$expected['store_id'] = $french_store;
		$expected['locale'] = 'fr_FR';

		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		// - STORE_ID=DEFAULT, LOCALE=DEFAULT
		$expected['store_id'] = 0;
		$expected['locale'] = 'en_US';
		
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $actual->getTranslationId());
		
		//Testing store_id and locale:
		$this->assertEquals($expected['store_id'], $actual->getStoreId());
		$this->assertEquals($expected['locale'], $actual->getLocale());
		
		//Resetting stuff:
		$actual->delete();
		$expected['store_id'] = $french_store;
		$expected['locale'] = 'fr_FR';
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		//STORE_ID=FALSE, LOCALE=FALSE
		unset($expected['locale']);
		unset($expected['store_id']);
		
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $actual->getTranslationId());
		
		//Testing store_id and locale:
		$this->assertEquals(0, $actual->getStoreId());
		$this->assertEquals('en_US', $actual->getLocale());
		
		//Resetting stuff:
		$actual->delete();
		$expected['store_id'] = $french_store;
		$expected['locale'] = 'fr_FR';
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));

		
		//Test without areas
		unset($expected['areas']);
		
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $actual->getTranslationId());
		
		//Testing areas:
		foreach (array('frontend', 'adminhtml', 'install') as $area) {
			$this->assertEquals(1, $actual->getData($area));
		}
		
		//Resetting stuff:
		$actual->delete();
		$expected['areas'] = array('frontend', 'adminhtml');
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		//Test with just a single area + strict
		$expected['areas'] = array('install');
		$expected['strict'] = true;
		
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $actual->getTranslationId());
		
		//Testing areas:
		foreach (array('frontend', 'adminhtml') as $area) {
			$this->assertEquals(0, $actual->getData($area));
		}
		$this->assertEquals(1, $actual->getInstall());
		
		//Resetting stuff:
		$actual->delete();
		$expected['areas'] = array('frontend', 'adminhtml');
		unset($expected['strict']);
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		//Test with just a single area and no strict
		$expected['areas'] = array('install');
		
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $actual->getTranslationId());
		
		//Testing areas:
		$this->assertEquals(1, $actual->getInstall());
		
		//Resetting stuff:
		$actual->delete();
		$expected['areas'] = array('frontend', 'adminhtml');
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		//Translation id combinations:
		// - With a translation id that equals zero
		$expected['translation_id'] = 0;
		
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $actual->getTranslationId());
		
		//Resetting stuff:
		$actual->delete();
		unset($expected['translation_id']);
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		// - With a translation id that doesn't exist
		$expected['translation_id'] = 800;
		
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $actual->getTranslationId());
		
		//Resetting stuff:
		$actual->delete();
		unset($expected['translation_id']);
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		// - With a translation id that exists but has major differences
		$new_string_id = Mage::getModel('translator/string')->setItem(array(
														'string' => 'temp', 
														'module' => 'Wheelbarrow_Temp'
													));
		$expected['string_id'] = $new_string_id;
		$this->translation->createItem($expected);
		
		$expected['translation_id'] = $this->translation->getCollection()->getLastItem()->getTranslationId();
		$expected['string_id'] = 1;
		
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $actual->getTranslationId());
		
		//Test it is NOT the same as the first:
		$this->assertNotEquals($expected['translation_id'], $actual->getTranslationId());
		
		//Resetting stuff:
		$actual->delete();
		$this->translation->load($expected['translation_id'])->delete();
		Mage::getModel('translator/string')->load($new_string_id)->delete();
		unset($expected['translation_id']);
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		// - With a translation id that exists and has minor differences
		$expected['translation'] = 'temp';
		$this->translation->createItem($expected);
		
		$expected['translation_id'] = $this->translation->getCollection()->getLastItem()->getTranslationId();
		
		//Test the temporary value:
		$this->assertEquals($expected['translation'], $this->translation->getCollection()->getLastItem()->getTranslation());
		
		$expected['translation'] = 'test';
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $actual->getTranslationId());
		
		//Test it IS the same as the first:
		$this->assertEquals($expected['translation_id'], $actual->getTranslationId());
		
		//Test the corrected value:
		$this->assertEquals($expected['translation'], $actual->getTranslation());
		
		//Resetting stuff:
		$actual->delete();
		unset($expected['translation_id']);
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		// - Without a translation id, but with the same item in store
		$this->translation->createItem($expected);
		
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $actual->getTranslationId());
		
		//Resetting stuff:
		$actual->delete();
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		// - Without a translation id, but with a similar item in store
		$this->translation->createItem($expected);
		
		//Test the temporary value:
		$this->assertEquals($expected['translation'], $this->translation->getCollection()->getLastItem()->getTranslation());
		
		$expected['translation'] = 'temp';
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $actual->getTranslationId());
		
		//Test the corrected value:
		$this->assertEquals($expected['translation'], $actual->getTranslation());
		
		//Resetting stuff:
		$actual->delete();
		$expected['translation'] = 'test';
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		// - Without a translation id, and with a completely different item in store
		$new_string_id = Mage::getModel('translator/string')->setItem(array(
														'string' => 'temp', 
														'module' => 'Wheelbarrow_Temp'
													));
		$expected['string_id'] = $new_string_id;
		$this->translation->createItem($expected);
		
		$first_item_id = $this->translation->getCollection()->getLastItem()->getTranslationId();
		//Test the temporary value:
		$this->assertEquals($expected['string_id'], $this->translation->getCollection()->getLastItem()->getStringId());
		
		$expected['string_id'] = 1;
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $actual->getTranslationId());
		
		//Test it is NOT the same as the first:
		$this->assertNotEquals($first_item_id, $actual->getTranslationId());
		
		//Test the corrected value:
		$this->assertEquals($expected['string_id'], $actual->getStringId());
		
		//Resetting stuff:
		$actual->delete();
		Mage::getModel('translator/string')->load($new_string_id)->delete();
		$this->translation->load($first_item_id)->delete();
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		//Test translation attribute options:
		// - Without a translation attribute
		unset($expected['translation']);
		
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test that it doesn't exist:
		$this->assertEquals(0, $actual->getTranslationId());
		
		//Resetting stuff:
		$actual->delete();
		$expected['translation'] = 'test';
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		// - With an empty translation attribute
		$expected['translation'] = '';
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test that it doesn't exist:
		$this->assertNull($actual->getTranslationId());
		
		//Resetting stuff:
		$actual->delete();
		$expected['translation'] = 'test';
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		// - With a null translation attribute
		$expected['translation'] = null;
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test that it doesn't exist:
		$this->assertNull($actual->getTranslationId());
		
		//Resetting stuff:
		$actual->delete();
		$expected['translation'] = 'test';
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		//Test different areas attribute options:
		// - Existing item in store with same areas and others, no strict
		$this->translation->createItem($expected);
		
		//@todo check that no unconfigured area gets set to true.
		
		$expected['areas'] = array('frontend');
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $actual->getTranslationId());
		
		//Resetting stuff:
		$actual->delete();
		$expected['areas'] = array('frontend', 'adminhtml');
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		// - Existing item in store with same areas and others, strict set to false
		$this->translation->createItem($expected);
		
		$expected['strict'] = false;
		$expected['areas'] = array('frontend');
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $actual->getTranslationId());
		
		//Resetting stuff:
		$actual->delete();
		unset($expected['strict']);
		$expected['areas'] = array('frontend', 'adminhtml');
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		// - Existing item in store with same areas and others, strict set to true
		$this->translation->createItem($expected);
		
		$expected['strict'] = true;
		$expected['areas'] = array('frontend');
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $actual->getTranslationId());
		
		//Test the updated areas attribute:
		foreach (array('frontend', 'adminhtml', 'install') as $area) {
			$this->assertEquals(
					in_array($area, $expected['areas']) ? 1 : 0,
					$actual->getData($area)
				);
		}
		
		//Resetting stuff:
		$actual->delete();
		unset($expected['strict']);
		$expected['areas'] = array('frontend', 'adminhtml');
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		// - Existing item in store with only other areas, no strict
		$expected['strict'] = true;
		$this->translation->createItem($expected);
		
		$first_item_id = $this->translation->getCollection()->getLastItem()->getTranslationId();
		
		//Test areas attribute:
		foreach (array('frontend', 'adminhtml', 'install') as $area) {
			$this->assertEquals(
					in_array($area, $expected['areas']) ? 1 : 0,
					$this->translation->load($first_item_id)->getData($area)
				);
		}
		
		$expected['areas'] = array('install');
		unset($expected['strict']);
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $actual->getTranslationId());
		
		//Test the updated areas attribute:
		foreach (array('frontend', 'adminhtml', 'install') as $area) {
			if (in_array($area, $expected['areas'])) {
				$this->assertEquals(1, $actual->getData($area));
			}
		}
		
		//Resetting stuff:
		$actual->delete();
		$this->translation->load($first_item_id)->delete();
		$expected['areas'] = array('frontend', 'adminhtml');
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		// - Existing item in store with only other areas, strict set to true
		$expected['strict'] = true;
		$this->translation->createItem($expected);
		
		$first_item_id = $this->translation->getCollection()->getLastItem()->getTranslationId();
		
		//Test areas attribute:
		foreach (array('frontend', 'adminhtml', 'install') as $area) {
			$this->assertEquals(
					in_array($area, $expected['areas']) ? 1 : 0, 
					$this->translation->load($first_item_id)->getData($area)
				);
		}
		
		$expected['areas'] = array('install');
		$this->translation->createItem($expected);
		$actual = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $actual->getTranslationId());
		
		//Test the updated areas attribute:
		foreach (array('frontend', 'adminhtml', 'install') as $area) {
			if (in_array($area, $expected['areas'])) {
				$this->assertEquals(1, $actual->getData($area));
			}
		}
		
		//Resetting stuff:
		$actual->delete();
		$this->translation->load($first_item_id)->delete();
		$expected['areas'] = array('frontend', 'adminhtml');
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		//@todo test removeDuplicateAreas():
		// Existing item with frontend, existing item with adminhtml. Now we're resaving the second
		// with frontend too. It should remove the one with adminhtml. 
		// Same test only the second has install too. This time it removes adminhtml from it, without
		// deleting it.
		
		//@todo test updateItem(), setItem(), etc.
	}
}