<?php

class Wheelbarrow_Translator_Helper_Sync extends Mage_Core_Helper_Abstract
{
	const PATH_SCAN_ACTION = 'add_path';
	const CSV_SCAN_ACTION = 'csv_pairs_scan';
	const THEME_SCAN_ACTION = 'theme_pairs_scan';
	
	/**
	 * A getter for the action identifier constants used with this helper.
	 * 
	 * The getter is required to allow access to these constants from an outer scope (from another 
	 * class). Using a getter here saves on the requirement for the PHP 5.3 '::' syntax.
	 * 
	 * @param string $const
	 * The name of the constant we wish to retrieve.
	 * 
	 * @return
	 * The value of that constant.
	 */
	public function getAction($const)
	{
		return constant('self::'.$const);
	}
	
	/**
	 * 
	 * Instantiates a cache item for use with the current sync action.
	 * 
	 * @param string $action_name
	 * The type of sync action that is instantiated.
	 * 
	 * @param mixed $data
	 * Where we'll save data specific to this type of sync action.
	 * 
	 * @return Wheelbarrow_Translator_Helper_Sync
	 */
	public function init($action_name, $data = '')
	{
		$register = array('state' => true, 'action' => $action_name, 'data' => $data);
		Mage::getModel('translator/cache')->createItem('sync', $register);
		
		return $this;
	}
	
	/**
	 * 
	 * Retrieves one cache item, holding a batch of string pairs for saving.
	 * 
	 * We're using AJAX to only save a small batch of items with each instantiation of the 
	 * Mage application. That way, we won't jam the server with thousands of requests. Any cache
	 * item stored in the db with a name of 'batch' is identified as a batch of items left for 
	 * saving.
	 * 
	 * @return mixed
	 * Either an array of items if a batch item was found or NULL if there aren't any left.
	 * 
	 */
	public function popBatch() {
		
		//Identify a batch item by its name, and retrieve just one item.
		$item = Mage::getModel('translator/cache')->getCollection()
			->addFieldToFilter('name', 'batch')
			->getLastItem();
		
		//The content (register) of the item is the part we'll be returning.
		$batch = unserialize($item->getRegister());
		
		//Remove the item so we won't re-save it again and again.
		$item->delete();
		
		return $batch;
	}
	
	/**
	 * 
	 * Set the contents of the cache item associated with the current sync action.
	 * 
	 * @param mixed $data
	 * 
	 * @return Wheelbarrow_Translator_Helper_Sync
	 * 
	 * @todo make sure it doesn't save to an empty item.
	 */
	public function setRegister($data) {
		
		// We're always retrieving the last item, to avoid inconsistencies if by accident 
		// there's more than one sync item in cache at a given time.
		$item = Mage::getModel('translator/cache')->getCollection()
			->addFieldToFilter('name', 'sync')
			->getLastItem()
			->setRegister(serialize($data))
			->save();
		
		return $this;
	}
	
	/**
	 * 
	 * Set only the data attribute of the current sync action's cache item.
	 * 
	 * @param mixed $data
	 * 
	 * @return Wheelbarrow_Translator_Helper_Sync
	 */
	public function setRegisterData($data) {
		
		//Again, always getting the last item to avoid inconsistencies.
		$item = Mage::getModel('translator/cache')->getCollection()
			->addFieldToFilter('name', 'sync')
			->getLastItem();
		
		//Extract the content (register) of the item
		$register = $item->getRegister();
		$register = unserialize($register);
		
		//Setting the 'data' attribute to its new value and re-saving it in the original item.
		$register['data'] = $data;
		$item->setRegister(serialize($register))->save();
		
		return $this;
	}
	
	/**
	 * 
	 * Get the contents of the current sync action's cache item.
	 * 
	 * @return mixed
	 * array if a sync action is in process, and an empty string otherwise.
	 */
	public function getRegister() {
		
		//Again, always getting the last item to avoid inconsistencies.
		$reg = Mage::getModel('translator/cache')->getCollection()
			->addFieldToFilter('name', 'sync')
			->getLastItem();
		
		//Extracting just the content (register) of that item.
		$reg = $reg->getRegister();
		$reg = unserialize($reg);
		
		return $reg;
	}
	
	/**
	 * 
	 * Main handler for sync callbacks.
	 * 
	 * When running db intense operations, we're using a sync.js script to constantly send sync
	 * requests until it is notified by the iterator function that there's no more data to process
	 * using the 'state' flag. Each call to sync, gets to the iterator, through which the right action
	 * is taken depending on the type specified in the sync cache item.
	 * 
	 * @return array
	 * The data to be sent back to sync.js in JSON format.
	 */
	public function iterator()
	{	
		//Get the sync action's cache item from the db.
		$register = $this->getRegister();
		
		//Set the $output array with the 
		$output = array();
		
		//@todo move this into the last case of the switch, and decide on a default output state.
		$output['state'] = $register['state'];

		switch ($register['action']) {
			case self::CSV_SCAN_ACTION:
			case self::THEME_SCAN_ACTION:
						
						//Retrieve a list of string pairs to save.
						$batch = $this->popBatch();
						
						if (is_array($batch) && count($batch)) {
							//Save the pairs to the db.
							Mage::getModel('translator/translate')->addMultipleEntries($batch);
							
							//Update the count, for outputing progress to the admin.
							$register['data']['completed'] += count($batch);
							$output['data'] = 'processed '.$register['data']['completed']. ' entries out of '.$register['data']['total'].' total.';
							$this->setRegister($register);
							
							//Mark the 'state' flag as true so that sync.js will call for the next batch.
							$output['state'] = true;
							break;
						} else {
							
							//@todo remove the sync item instead of emptying it out (and change the test for that).
							$this->setRegister(array('state' => false));
							
							// Set the 'state' flag to false so it won't re-run 
							// as there aren't any batches left.
							$output['state'] = false;
							break;
						}
				case self::PATH_SCAN_ACTION:
					
					//Update the admin message area with the new results from the observer.
					$output['data'] = $register['data']['message'];
					
					//If this was the first run, mark this flag as false so it won't re-open the page.
					if ($register['data']['go_to_url']) {
						$output['url'] = $register['data']['path'];
						$register['data']['go_to_url'] = false;
						$this->setRegister($register);
					}
					break;
		}
		
		return $output;
	}
}