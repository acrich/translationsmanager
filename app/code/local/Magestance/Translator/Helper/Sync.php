<?php

class Magestance_Translator_Helper_Sync extends Mage_Core_Helper_Abstract
{
	const PATH_SCAN_ACTION = 'add_path';
	const CSV_SCAN_ACTION = 'csv_pairs_scan';
	const CSV_QUEUE_NAME = 'csv_files_pairs';
	const THEME_SCAN_ACTION = 'theme_pairs_scan';
	const THEME_QUEUE_NAME = 'theme_files_pairs';
	
	public function init($action_name)
	{
		$queue = Mage::helper('translator/queue');
		$queue->init('sync');
		$queue->push('sync', array('state' => true, 'action' => $action_name));
	}

	public function iterator()
	{
		$queue_model = Mage::helper('translator/queue');	
		$queue = $queue_model->getFirst('sync');
		
		$output = array();
		$output['state'] = $queue['state'];

		switch ($queue['action']) {
			case self::CSV_SCAN_ACTION:
					$batch = $queue_model->getBatch(self::CSV_QUEUE_NAME);

					if (count($batch)) {
						Mage::getModel('translator/translate')->addMultipleEntries($batch);
						
						$queue['data']['completed'] += count($batch);
						$queue_model->setFirst('sync', $queue);
						
						$output['data'] = 'processed '.$queue['data']['completed']. ' entries out of '.$queue['data']['total'].' total.';
						$output['state'] = true;
						break;
					} else {
						//@todo remove close().
						$this->close();
						$output['state'] = false;
						break;
					}
			case self::THEME_SCAN_ACTION:
						$batch = $queue_model->getBatch(self::THEME_QUEUE_NAME);
					
						if (count($batch)) {
							Mage::getModel('translator/translate')->addMultipleEntries($batch);
					
							$queue['data']['completed'] += count($batch);
							$queue_model->setFirst('sync', $queue);
					
							$output['data'] = 'processed '.$queue['data']['completed']. ' entries out of '.$queue['data']['total'].' total.';
							$output['state'] = true;
							break;
						} else {
							//@todo remove close().
							$this->close();
							$output['state'] = false;
							break;
						}
				case self::PATH_SCAN_ACTION:
					$output['data'] = $queue['data']['message'];
					if ($queue['data']['go_to_url']) {
						$output['url'] = $queue['data']['path'];
						$queue['data']['go_to_url'] = false;
						$queue_model->setFirst('sync', $queue);
					}
					break;
		}
		
		return $output;
	}
	
	public function close()
	{
		$register = Mage::helper('translator/queue')->pop('sync');
		$register['state'] = false;
		Mage::helper('translator/queue')->push('sync', $register);
	}
}