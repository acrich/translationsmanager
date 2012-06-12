<?php

class Magestance_Demo_Helper_Queue extends Mage_Core_Helper_Abstract
{	
	public function init($queue_id) {
		$data = array();
		Mage::getModel('demo/cache')->createItem($queue_id, serialize($data));
	}
	
	public function prepareBatches($queue_id, $batch_length) {
		$model = Mage::getModel('demo/cache')->getCollection()
			->addFieldToFilter('name', $queue_id)
			->getLastItem();
		
		$register = unserialize($model->getRegister());
		
		$register = array_chunk($register, $batch_length);

		$model->setRegister(serialize($register))->save();
	}
	
	public function getBatch($queue_id) {
		$model = Mage::getModel('demo/cache')->getCollection()
			->addFieldToFilter('name', 'csv_files_pairs')
			->getLastItem();

		$register = unserialize($model->getRegister());
		$batch = array_pop($register);

		$model->setRegister(serialize($register))->save();

		return $batch;
	}
	
	public function push($queue_id, $data) {
		$model = Mage::getModel('demo/cache')->getCollection()
			->addFieldToFilter('name', $queue_id)
			->getFirstItem();
		
		$register = unserialize($model->getRegister());
		
		$register[] = $data;

		$model->setRegister(serialize($register))->save();
	}
	
	public function pushMultiple($queue_id, $data) {
		$model = Mage::getModel('demo/cache')->getCollection()
			->addFieldToFilter('name', $queue_id)
			->getFirstItem();
		
		$register = unserialize($model->getRegister());

		$new_register = array_merge($register, (array)$data);
		
		$model->setRegister(serialize($new_register))->save();
	}
	
	public function pop($queue_id) {
		$model = Mage::getModel('demo/cache')->getCollection()
			->addFieldToFilter('name', $queue_id)
			->getFirstItem();
		
		$register = unserialize($queue->getRegister());
	
		$element = array_pop($register);
	
		$model->setRegister(serialize($register))->save();
		
		return $element;
	}
	
	public function popAndPush($queue_id) {
		$model = Mage::getModel('demo/cache')->getCollection()
			->addFieldToFilter('name', $queue_id)
			->getFirstItem();
		
		$register = unserialize($model->getRegister());

		return $register[0];
	}
	
	public function clean($queue_id) {
		$data = array();
		$model = Mage::getModel('demo/cache')->getCollection()
			->addFieldToFilter('name', $queue_id)
			->getFirstItem()
			->setRegister(serialize($data))
			->save();
	}
	
	public function replace($queue_id, $replacement) {
		Mage::getModel('demo/cache')
			->getCollection()
			->addFieldToFilter('name', $queue_id)
			->getLastItem()
			->setRegister(serialize($replacement))
			->save();
	}
}