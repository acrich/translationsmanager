<?php
require_once 'PHPUnit/Autoload.php';
require_once '/var/www/translator/app/Mage.php';
 
class Wheelbarrow_Translator_Model_Translation_Test extends PHPUnit_Framework_TestCase {
	 
	public function setUp()
	{
		Mage::app('default');
		$this->translation = Mage::getModel('translator/translation');
		
		$this->string_id = Mage::getModel('translator/string')->createItem(array(
															'string' => 'test', 
															'module' => 'Wheelbarrow_Test'
														));
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
				'string_id' => $this->string_id
		);
		
		//Test without a string id
		unset($expected['string_id']);
		$this->translation->createItem($expected);
		
		$id = $this->translation->getCollection()->getLastItem()->getTranslationId();
		
		$this->assertNull($id);
		
		//Resetting stuff:
		$expected['string_id'] = $this->string_id;

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
		
		//@todo make sure we're always calling setItem instead of the other two, so we'll be able to check just
		// setItem and updateItem with translation_id and none of the ones below. P.S. why should it ignore the
		// translation_id in case of major differences?
		
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
		$expected['string_id'] = $this->string_id;
		
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
		
		$expected['string_id'] = $this->string_id;
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
		
		//removeDuplicateAreas() Triggers:
		// - createItem doesn't remove anything when creating a new item, but does remove the
		//	extra item when updating.
		
		$expected['areas'] = array('frontend');
		$this->translation->createItem($expected);
		$first_item_id = $this->translation->getCollection()->getLastItem()->getTranslationId();
		
		//Test it exists:
		$this->assertGreaterThan(0, $first_item_id);
		
		$expected['areas'] = array('adminhtml');
		$this->translation->createItem($expected);
		$second_item = $this->translation->getCollection()->getLastItem();
		
		//Test that both items exist:
		$this->assertGreaterThan(0, $first_item_id);
		$this->assertGreaterThan(0, $second_item->getTranslationId());
		
		//Test the current value:
		$this->assertEquals($expected['translation'], $second_item->getTranslation());
		
		$expected['translation'] = 'temp';
		$expected['areas'] = array('frontend', 'adminhtml');
		$this->translation->createItem($expected);
		
		$third_item = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $third_item->getTranslationId());
		
		//Test the current value:
		$this->assertEquals($expected['translation'], $third_item->getTranslation());
		
		//Test that no other items exist:
		$third_item->delete();
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		//Resetting stuff:
		$expected['translation'] = 'test';
		
		// - same for updateItem()
		
		//Create first item:
		$expected['areas'] = array('frontend');
		$this->translation->createItem($expected);
		$first_item_id = $this->translation->getCollection()->getLastItem()->getTranslationId();

		//Create second item:
		$expected['areas'] = array('adminhtml');
		$this->translation->createItem($expected);
		$second_item = $this->translation->getCollection()->getLastItem();
		
		//Test that both items exist:
		$this->assertGreaterThan(0, $first_item_id);
		$this->assertGreaterThan(0, $second_item->getTranslationId());
		

		$expected['translation_id'] = $second_item->getTranslationId();
		$expected['areas'] = array('frontend', 'adminhtml');
		$this->translation->updateItem($expected);
		
		$third_item = $this->translation->getCollection()->getLastItem();
		
		//Test it exists:
		$this->assertGreaterThan(0, $third_item->getTranslationId());
		
		//Test that no other items exist:
		$third_item->delete();
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		//Resetting stuff:
		unset($expected['translation_id']);
		
		// - when the extra item has another area set, it won't be removed, just updated:
		
		//Create first item:
		$expected['areas'] = array('frontend');
		$this->translation->createItem($expected);
		$first_item_id = $this->translation->getCollection()->getLastItem()->getTranslationId();
		
		//Test it exists:
		$this->assertGreaterThan(0, $first_item_id);
		
		//Create second item:
		$expected['areas'] = array('adminhtml', 'install');
		$this->translation->createItem($expected);
		$second_item_id = $this->translation->getCollection()->getLastItem()->getTranslationId();
		
		//Test that both items exist:
		$this->assertGreaterThan(0, $first_item_id);
		$this->assertGreaterThan(0, $second_item_id);
		
		$expected['areas'] = array('frontend', 'adminhtml');
		$this->translation->createItem($expected);
		
		$third_item = $this->translation->getCollection()->getLastItem();
		
		//Test that the new item has both frontend and adminhtml:
		$this->assertEquals(1, $third_item->getFrontend());
		$this->assertEquals(1, $third_item->getAdminhtml());
		$this->assertEquals(0, $third_item->getInstall());

		//Test that the second item only has install set:
		$second_item = $this->translation->load($second_item_id);
		$this->assertEquals(0, $second_item->getFrontend());
		$this->assertEquals(0, $second_item->getAdminhtml());
		$this->assertEquals(1, $second_item->getInstall());
		
		//Test that there aren't three items instead of just two:
		$this->assertEquals(2, count($this->translation->getCollection()->load()));
		
		//Resetting stuff:
		$third_item->delete();
		$second_item->delete();
		
		// - same for updateItem():
		
		//Create first item:
		$expected['areas'] = array('frontend');
		$this->translation->createItem($expected);
		$first_item_id = $this->translation->getCollection()->getLastItem()->getTranslationId();
		
		//Test it exists:
		$this->assertGreaterThan(0, $first_item_id);
		
		//Create second item:
		$expected['areas'] = array('adminhtml', 'install');
		$this->translation->createItem($expected);
		$second_item_id = $this->translation->getCollection()->getLastItem()->getTranslationId();
		
		//Test it exists:
		$this->assertGreaterThan(0, $second_item_id);
		
		//Update the first item:
		$expected['areas'] = array('frontend', 'adminhtml');
		$expected['translation_id'] = $first_item_id;
		$this->translation->updateItem($expected);
		
		$first_item = $this->translation->load($first_item_id);
		
		//Test that the new item has both frontend and adminhtml:
		$this->assertEquals(1, $first_item->getFrontend());
		$this->assertEquals(1, $first_item->getAdminhtml());
		$this->assertEquals(0, $first_item->getInstall());
		
		//Test that the second item only has install set:
		$second_item = $this->translation->load($second_item_id);
		$this->assertEquals(0, $second_item->getFrontend());
		$this->assertEquals(0, $second_item->getAdminhtml());
		$this->assertEquals(1, $second_item->getInstall());
		
		//Test that there aren't three items instead of just two:
		$this->assertEquals(2, count($this->translation->getCollection()->load()));
		
		//Resetting stuff:
		$first_item->delete();
		$second_item->delete();
		unset($expected['translation_id']);
		
		// updateItem() tests:
		// - no translation attribute:
		
		//Create first item:
		$this->translation->createItem($expected);
		$first_item = $this->translation->getCollection()->getLastItem();
		
		//Update without a translation attribute:
		$expected['translation_id'] = $first_item->getTranslationId();
		unset($expected['translation']);
		$this->translation->updateItem($expected);
		
		//Check that the item still has a translation:
		$expected['translation'] = 'test';
		$first_item = $this->translation->load($expected['translation_id']);
		$this->assertEquals($expected['translation'], $first_item->getTranslation());
		
		//Resetting stuff:
		$first_item->delete();
		unset($expected['translation_id']);
		
		// - empty translation attribute:
		
		//Create first item:
		$this->translation->createItem($expected);
		$first_item = $this->translation->getCollection()->getLastItem();
		
		//Update without a translation attribute:
		$expected['translation_id'] = $first_item->getTranslationId();
		$expected['translation'] = '';
		$this->translation->updateItem($expected);
		
		//Check that the item still has a translation:
		$expected['translation'] = 'test';
		$first_item = $this->translation->load($expected['translation_id']);
		$this->assertEquals($expected['translation'], $first_item->getTranslation());
		
		//Resetting stuff:
		$first_item->delete();
		unset($expected['translation_id']);
		
		// - translation_id that doesn't exist:
		
		//Update without a translation attribute:
		$expected['translation_id'] = 666;
		$result = $this->translation->updateItem($expected);
		
		//Check that it returned false instead of running:
		$this->assertEquals(0, $result);
		
		//Resetting stuff:
		unset($expected['translation_id']);
		
		// - normal behaviour:
	
		//Create item:
		$this->translation->createItem($expected);
		$item = $this->translation->getCollection()->getLastItem();
		
		//Update:
		$expected['translation_id'] = $item->getTranslationId();
		$expected['translation'] = 'temp';
		$expected['areas'] = array('frontend');
		$this->translation->updateItem($expected);
		
		//Check the values:
		$item = $this->translation->load($expected['translation_id']);
		$this->assertEquals($expected['translation'], $item->getTranslation());
		$this->assertEquals(1, $item->getFrontend());
		$this->assertEquals(1, $item->getAdminhtml());
		$this->assertEquals(0, $item->getInstall());

		//Update again, but with a new area set:
		$expected['areas'] = array('frontend', 'install');
		$this->translation->updateItem($expected);
		
		//Check the values:
		$item = $this->translation->load($expected['translation_id']);
		$this->assertEquals(1, $item->getFrontend());
		$this->assertEquals(1, $item->getAdminhtml());
		$this->assertEquals(1, $item->getInstall());
		
		//Resetting stuff:
		$item->delete();
		$expected['areas'] = array('frontend', 'adminhtml');
		$expected['translation'] = 'test';
		unset($expected['translation_id']);
		
		//Make sure there aren't any extra items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		//Test setItem():
		
		// - translation_id isn't set:
		$this->translation->setItem($expected);
		$id = $this->translation->getCollection()->getLastItem()->getTranslationId();
		
		//Make sure it created an item:
		$this->assertGreaterThan(0, $id);
		
		// - translation_id set to zero:
		$expected['translation_id'] = 0;
		$expected['store_id'] = 1;
		$this->translation->setItem($expected);
				
		//Make sure it created a new item:
		$second_id = $this->translation->getCollection()->getLastItem()->getTranslationId();
		$this->assertNotEquals($id, $second_id);
		
		// - translation_id set:
		$expected['translation_id'] = $id;
		$expected['translation'] = 'temp';
		$expected['store_id'] = $french_store;
		$this->translation->setItem($expected);
		$updated_item = $this->translation->load($expected['translation_id']);
		
		//Make sure the translation got updated:
		$this->assertEquals($expected['translation'], $updated_item->getTranslation());
		
		//Resetting stuff:
		$this->translation->load($id)->delete();
		$this->translation->load($second_id)->delete();
		$expected['translation'] ='test';
		unset($expected['translation_id']);
	
	
		//Test getIdByParams():
	
		// - without store_id, without areas
		
		//Create an item:
		$this->translation->setItem($expected);
		$actual = $this->translation->getCollection()->getLastItem()->getTranslationId();
		
		//Find it:
		unset($expected['store_id']);
		unset($expected['areas']);
		$id = $this->translation->getIdByParams($expected);
	
		//Check that it wasn't found:
		$this->assertFalse($id);
			
		// - with store_id, without areas
		$expected['store_id'] = $french_store;
		$id = $this->translation->getIdByParams($expected);
		
		//Check that it was found:
		$this->assertEquals($id, $actual);
		
		// - with store_id, with areas (no item exists)
		$this->translation->load($actual)->delete();
		$id = $this->translation->getIdByParams($expected);
		
		//Check that it wasn't found:
		$this->assertFalse($id);
		
		// - with store_id, with areas (an item exist with other areas)
		$expected['areas'] = array('frontend', 'adminhtml');
		$this->translation->setItem($expected);
		$actual = $this->translation->getCollection()->getLastItem()->getTranslationId();
		
		$expected['areas'] = array('install');
		$id = $this->translation->getIdByParams($expected);
		
		//Check that it wasn't found:
		$this->assertFalse($id);
		
		// - with store_id, wih areas (the exact item exists)
		$expected['areas'] = array('frontend','adminhtml');
		$id = $this->translation->getIdByParams($expected);
		
		//Check that it wasn't found:
		$this->assertEquals($id, $actual);
		
		//Test deleteTranslation()
		
		// - translation_id set
		
		//Create the item:
		$this->translation->setItem($expected);
		
		//Make sure that there's an item:
		$this->assertEquals(1, count($this->translation->getCollection()->load()));
		
		//Delete it:
		$expected['translation_id'] = $this->translation->getCollection()->getLastItem()->getTranslationId();
		$this->translation->deleteTranslation($expected);
		
		//Make sure there aren't any items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		// - translation_id not set, string_id not set
		
		//Create the item:
		unset($expected['translation_id']);
		$this->translation->setItem($expected);
		
		//Try to delete the item:
		unset($expected['string_id']);
		$this->translation->deleteTranslation($expected);
		
		//Check that it wasn't deleted:
		$this->assertEquals(1, count($this->translation->getCollection()->load()));
		
		// - translation_id not set, string_id set, store_id is null
		$expected['string_id'] = $this->string_id;
		$expected['store_id'] = null;
		$this->translation->deleteTranslation($expected);
		
		//Check that it was deleted:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		// - translation_id not set, string_id set, store_id set
		$expected['store_id'] = $french_store;
		$this->translation->setItem($expected);
		
		//Make sure that there's an item:
		$this->assertEquals(1, count($this->translation->getCollection()->load()));
		
		$this->translation->deleteTranslation($expected);
		
		//Make sure there aren't any items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		// - two items match
		
		//Create the items:
		$this->translation->setItem($expected);
		$expected['areas'] = array('install');
		$this->translation->setItem($expected);
		
		//Make sure that there are items:
		$this->assertEquals(2, count($this->translation->getCollection()->load()));
		
		$this->translation->deleteTranslation($expected);

		//Make sure there aren't any items:
		$this->assertEquals(0, count($this->translation->getCollection()->load()));
		
		// - no items match
		$this->translation->deleteTranslation($expected);
		
		//Cleanup:
		$expected['areas'] = array('frontend', 'adminhtml');
		
		//Test getTranslatedStringsByStore():
		
		//Check that it returns zero when no items match:
		$string_ids = $this->translation->getTranslatedStringsByStore(0);
		$this->assertEquals(array(), $string_ids);
		
		//Check that it returns one when there's an item:
		$this->translation->setItem($expected);
		$item = $this->translation->getCollection()->getLastItem();
		$string_ids = $this->translation->getTranslatedStringsByStore(2);
		$this->assertEquals(array($item->getStringId()), $string_ids);
		
		//Cleanup:
		$item->delete();
		
		//Test getTranslationsByStringId():
		$this->assertEquals(0, count($this->translation->getTranslationsByStringId($this->string_id)));
		
		$this->translation->setItem($expected);
		$item = $this->translation->getCollection()->getLastItem();
		$this->assertEquals(1, count($this->translation->getTranslationsByStringId($this->string_id)));
		
		$this->assertEquals(0, count($this->translation->getTranslationsByStringId(666)));
		
		//Cleanup:
		$item->delete();
		
		//Test getStringIdsByArea():
		
		$this->assertEquals(0, count($this->translation->getStringIdsByArea('frontend')));
		
		$this->translation->setItem($expected);
		$item = $this->translation->getCollection()->getLastItem();
		
		$this->assertEquals(1, count($this->translation->getStringIdsByArea('frontend')));
		
		//Cleanup:
		$item->delete();
		
		//Test getDuplicatesList():
		
		$this->assertEquals('', $this->translation->getDuplicatesList($french_store));
		
		$expected['areas'] = array('frontend');
		$this->translation->setItem($expected);
		$items = array($this->translation->getCollection()->getLastItem()->getTranslationId());
		
		$expected['areas'] = array('install');
		$this->translation->setItem($expected);
		$items[] = $this->translation->getCollection()->getLastItem()->getTranslationId();
		
		$this->assertEquals(1, count(explode(',', $this->translation->getDuplicatesList($french_store))));
		
		$expected['areas'] = array('adminhtml');
		$this->translation->setItem($expected);
		$items[] = $this->translation->getCollection()->getLastItem()->getTranslationId();
		
		$this->assertEquals(2, count(explode(',', $this->translation->getDuplicatesList($french_store))));
		
		//Cleanup:
		foreach ($items as $item) {
			$this->translation->load($item)->delete();
		}
	
	}
	
	public function tearDown()
	{
		Mage::getModel('translator/string')->load($this->string_id)->delete();
	}
}