<?php
require_once 'PHPUnit/Autoload.php';
require_once '/var/www/translator/app/Mage.php';
 
class Wheelbarrow_Translator_Helper_Data_Test extends PHPUnit_Framework_TestCase {
	 
	public function setUp()
	{
		Mage::app('default');
		$this->dataHelper = Mage::helper('translator');
	}

	/**
	 * Tests helper methods to getting and setting the currently stored store_id.
	 * 
	 * Both the translations grid and the edit translation form have a store view switcher. 
	 * The value chosen there should persist across page navigations. It is also extracted when
	 * saving a translation item.
	 * 
	 * This test checks that the getter supplies a value and that that value is the default store id
	 * initialy. It then checks that one can set the current store id to another value.
	 * 
	 * No checks are made for proper input and output values so the tests do not cover those scenarios.
	 * 
	 */
	public function testCurrentStore()
	{
		//Running with the default value.
		$expected = $this->dataHelper->getStoredSession('store');
		$actual = Mage_Core_Model_App::ADMIN_STORE_ID;
		$this->assertEquals($expected, $actual);
	
		//Setting a value.
		$modified = 1;
		$this->dataHelper->setStoredSession('store', $modified);
		$expected = $this->dataHelper->getStoredSession('store');
		$this->assertEquals($expected, $modified);
	}

	/**
	 * Tests helper methods to getting and setting the currently stored area.
	 *
	 * Each translation item can be assigned to multiple areas (frontend, adminhtml, install).
	 * The current area chosen through the switcher blocks controlls what value is viewed in the
	 * translation field and where that value is to be saved (in case different areas are assigned to
	 * different translation items).
	 * 
	 * The currently selected area scope is saved in a session store and used for the translations grid
	 * and the edit translation form, as well as in the controller's save action.
	 *
	 */
	public function testCurrentArea()
	{
		//Running with the default value.
		$expected = $this->dataHelper->getStoredSession('area');
		$actual = '';
		$this->assertEquals($expected, $actual);
	
		//Setting a value.
		$modified = 'frontend';
		$this->dataHelper->setStoredSession('store', $modified);
		$expected = $this->dataHelper->getStoredSession('store');
		$this->assertEquals($expected, $modified);
		
		//Setting another value.
		$modified = 'install';
		$this->dataHelper->setStoredSession('store', $modified);
		$expected = $this->dataHelper->getStoredSession('store');
		$this->assertEquals($expected, $modified);
	}
}