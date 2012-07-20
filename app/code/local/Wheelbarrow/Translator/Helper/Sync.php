<?php

class Wheelbarrow_Translator_Helper_Sync extends Mage_Core_Helper_Abstract
{
	const PATH_SCAN_ACTION = 'add_path';
	const CSV_SCAN_ACTION = 'csv_pairs_scan';
	const THEME_SCAN_ACTION = 'theme_pairs_scan';
	
	public function init($action_name, $data = '')
	{
		$register = array('state' => true, 'action' => $action_name, 'data' => $data);
		Mage::getModel('translator/cache')->createItem('sync', $register);
	}
	
	public function popBatch() {
		$item = Mage::getModel('translator/cache')->getCollection()
			->addFieldToFilter('name', 'batch')
			->getLastItem();
		$batch = unserialize($item->getRegister());
		$item->delete();
		return $batch;
	}
	
	public function setRegister($data) {
		$item = Mage::getModel('translator/cache')->getCollection()
			->addFieldToFilter('name', 'sync')
			->getLastItem()
			->setRegister(serialize($data))
			->save();
	}
	
	public function setRegisterData($data) {
		$item = Mage::getModel('translator/cache')->getCollection()
			->addFieldToFilter('name', 'sync')
			->getLastItem();
		$register = $item->getRegister();
		$register = unserialize($register);
		$register['data'] = $data;
		$item->setRegister(serialize($register))->save();
		
	}
	
	public function getRegister() {
		$reg = Mage::getModel('translator/cache')->getCollection()
			->addFieldToFilter('name', 'sync')
			->getLastItem();
		$reg = $reg->getRegister();
		$reg = unserialize($reg);
		return $reg;
	}

	public function iterator()
	{	
		$register = $this->getRegister();
		
		$output = array();
		$output['state'] = $register['state'];

		switch ($register['action']) {
			case self::CSV_SCAN_ACTION:
					$batch = $this->popBatch();
					if (is_array($batch) && count($batch)) {
						Mage::getModel('translator/translate')->addMultipleEntries($batch);
						
						$register['data']['completed'] += count($batch);
						$this->setRegister($register);
						
						$output['data'] = 'processed '.$register['data']['completed']. ' entries out of '.$register['data']['total'].' total.';
						$output['state'] = true;
						break;
					} else {
						//@todo remove close().
						$this->close();
						$output['state'] = false;
						break;
					}
			case self::THEME_SCAN_ACTION:
						$batch = $this->popBatch();
						if (is_array($batch) && count($batch)) {
							Mage::getModel('translator/translate')->addMultipleEntries($batch);
					
							$register['data']['completed'] += count($batch);
							$this->setRegister($register);
					
							$output['data'] = 'processed '.$register['data']['completed']. ' entries out of '.$register['data']['total'].' total.';
							$output['state'] = true;
							break;
						} else {
							//@todo remove close().
							$this->close();
							$output['state'] = false;
							break;
						}
				case self::PATH_SCAN_ACTION:
					$output['data'] = $register['data']['message'];
					if ($register['data']['go_to_url']) {
						$output['url'] = $register['data']['path'];
						$register['data']['go_to_url'] = false;
						$this->setRegister($register);
					}
					break;
		}
		
		return $output;
	}
	
	public function close()
	{
		$this->setRegister(array('state' => false));
	}
}