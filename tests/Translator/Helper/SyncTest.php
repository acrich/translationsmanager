<?php
require_once 'PHPUnit/Autoload.php';
require_once '/var/www/translator/app/Mage.php';
 
class Wheelbarrow_Translator_Helper_Sync_Test extends PHPUnit_Framework_TestCase {
	
	public function setUp()
	{
		Mage::app('default');
		$this->syncHelper = Mage::helper('translator/sync');
	}

	/**
	 * Test the getter and setter functions for the sync helper's cache item.
	 * 
	 * The sync helper uses a db cache item for data persistence accross POST requests from sync.js.
	 * This is used for handling potentially large amounts of data with multiple http requests, so that
	 * the server won't crash in case an action takes more than the allocated HTTP timeout.
	 * 
	 * This test covers the getter and setter for the cache item associated with the sync action, as
	 * well as a setter for just the data attribute on that item, and its init method.
	 * 
	 * @todo change the function names, they don't properly explain what we're doing.
	 */
	public function testSyncRegister()
	{
		
		//Trying to get the contents before saving an item.
		$actual = $this->syncHelper->getRegister();
		$expected_1 = array('state' => false);
		$expected_2 = false;
		//@todo decide on an option and make sure it returns only that.
		$this->assertThat(
				$actual,
				$this->logicalOr(
						$this->equalTo($expected_1),
						$this->equalTo($expected_2)
				)
		);

		//Instantiating a sync cache item.
		$action_name = 'temp';
		$data = array('first' => 'some value', 'second' => array('first' => 'another value'));
		$actual = $this->syncHelper->init($action_name, $data)->getRegister();
		$expected = array('state' => true, 'action' => $action_name, 'data' => $data);
		$this->assertEquals($expected, $actual);
		
		//Setting the register to a modified value.
		$expected['state'] = false;
		$actual = $this->syncHelper->setRegister($expected)->getRegister();
		$this->assertEquals($expected, $actual);
		
		//@todo get rid of this function.
		//Setting only the data portion of the sync cache item.
		$data['third'] = false;
		$data['first'] = 123;
		$actual = $this->syncHelper->setRegisterData($data)->getRegister();
		$expected['data'] = $data;
		$this->assertEquals($expected, $actual);
		
		return $expected;
	}
	
	/**
	 * 
	 * Tests the work with batches of string pairs, used for spanning intensive operations over
	 * multiple HTTP requests. Each batch contains an array with items, each of which is an array
	 * consisting of a string-translation pair data from a translations .csv file.
	 * 
	 * This test is just for the getter function for batches.
	 * Saving them is done elsewhere.
	 * @todo create a single place where handling batches is accomplished.
	 * @todo decide on a return value when the getter has no db items to use (see sec. #1 below)
	 * 
	 * Rules:
	 * 1. Retrieving the batches when there aren't any should return an empty array or false.
	 * 2. After saving multiple batches, retrieving should return and remove one batch each time.
	 */
	public function testBatchesGetter()
	{
		//Check the getter before we inserted anything.
		$actual = $this->syncHelper->popBatch();
		$expected = false;
		$this->assertEquals($expected, $actual);
		
		//Set items in the db.
		$item = array('first' => 'false', 'second' => array('third' => 'some value'));
		foreach (array($item, $item, $item) as $batch) {
			Mage::getModel('translator/cache')->createItem('batch', $batch);
		}
		
		//Now get them one by one until none are left.
		$count = 3;
		while ($count > 0) {
			$actual = $this->syncHelper->popBatch();
			$this->assertEquals($item, $actual);
			$count--;
		}
		
		//Check that now it's empty
		$actual = $this->syncHelper->popBatch();
		$this->assertEquals($expected, $actual);
	}
	
	/**
	 * @depends testSyncRegister
	 */
	public function testIterator($register)
	{
		//Test with no sync cache item in store.
		$output = $this->syncHelper->iterator();
		$this->assertEquals(false, $output['state']);
		
		//Test a module csv scan sync action
		//  Without a batch item in store
		$register = array(	'state' => true, 
							'action' => $this->syncHelper->getAction('CSV_SCAN_ACTION'),
							'data' => array(
					    			'completed' => 0,
					    			'total' => 1
		    			));
		$this->syncHelper->setRegister($register);
		
		$output = $this->syncHelper->iterator();
		$this->assertEquals(false, $output['state']);
		
		//  With a batch item in store
		$batch = array(array(
					'string' => 'temp',
					'translation' =>'temp',
					'locale' => 'en_US',
					'areas' => array('frontend'),
					'strict' => true,
					'module' => 'Mage_Core'
				));
		Mage::getModel('translator/cache')->createItem('batch', $batch);
		$this->syncHelper->setRegister($register);
		
		$output = $this->syncHelper->iterator();
		$this->assertEquals(true, $output['state']);
		$this->assertEquals('processed 1 entries out of 1 total.', $output['data']);
		
		//Test a theme csv scan sync action
		//  Without a batch item in store
		$register = array(	'state' => true,
				'action' => $this->syncHelper->getAction('THEME_SCAN_ACTION'),
				'data' => array(
						'completed' => 0,
						'total' => 1
				));
		$this->syncHelper->setRegister($register);
		
		$output = $this->syncHelper->iterator();
		$this->assertEquals(false, $output['state']);
		
		//  With a batch item in store
		$batch = array(array(
				'string' => 'temp',
				'translation' =>'temp',
				'locale' => 'en_US',
				'areas' => array('frontend'),
				'strict' => true,
				'module' => 'Mage_Core'
		));
		Mage::getModel('translator/cache')->createItem('batch', $batch);
		$this->syncHelper->setRegister($register);
		
		$output = $this->syncHelper->iterator();
		$this->assertEquals(true, $output['state']);
		$this->assertEquals('processed 1 entries out of 1 total.', $output['data']);
		
		//Test a path scan action
		//  With the 'go_to_url' flag set to true
		$register = array(	'state' => true,
				'action' => $this->syncHelper->getAction('PATH_SCAN_ACTION'),
				'data' => array(
						'message' => 'some message',
						'go_to_url' => true,
						'path' => 'some_path'
				));
		$this->syncHelper->setRegister($register);
		
		$output = $this->syncHelper->iterator();
		$this->assertTrue($output['state']);
		$this->assertEquals($register['data']['message'], $output['data']);
		$this->assertEquals($register['data']['path'], $output['url']);
		$register = $this->syncHelper->getRegister();
		$this->assertFalse($register['data']['go_to_url']);
		
		//  With the 'go_to_url' flag set to false
		$output = $this->syncHelper->iterator();
		$this->assertTrue($output['state']);
		$this->assertEquals($register['data']['message'], $output['data']);
		$register = $this->syncHelper->getRegister();
		$this->assertFalse($register['data']['go_to_url']);
	}
	
	public function tearDown()
	{
		//Remove the sync cache item we created before.
		Mage::getModel('translator/cache')->getCollection()
			->addFieldToFilter('name', 'sync')
			->getLastItem()
			->delete();
	}
}