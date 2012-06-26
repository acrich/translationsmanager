<?php

class Magestance_Demo_Helper_Sync extends Mage_Core_Helper_Abstract
{
	
	public function init($action_name)
	{
		$queue = Mage::helper('demo/queue');
		$queue->init('sync');
		$queue->push('sync', array('state' => true, 'action' => $action_name));
	}

	public function iterator()
	{
		$queue_model = Mage::helper('demo/queue');
		$output = array();
		
		$queue = $queue_model->getFirst('sync');

		$output['state'] = $queue['state'];

		switch ($queue['action']) {
			case 'csv_pairs_scan':
					$batch = $queue_model->getBatch('csv_files_pairs');

					if (count($batch)) {
						Mage::getModel('demo/translate')->addMultipleEntries($batch);
						
						$queue['data']['completed'] += count($batch);
						$queue_model->setFirst('sync', $queue);
						
						$output['data'] = 'processed '.$queue['data']['completed']. ' entries out of '.$queue['data']['total'].' total.';
						$output['state'] = true;
						break;
					} else {
						$this->close();
						$output['state'] = false;
						break;
					}
				case 'add_path':
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
		$register = Mage::helper('demo/queue')->pop('sync');
		$register['state'] = false;
		Mage::helper('demo/queue')->push('sync', $register);
	}
}