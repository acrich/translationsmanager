<?php

class Magestance_Demo_Helper_Queue extends Mage_Core_Helper_Abstract
{	
	public function init($queue_id) {
		
		$data = array();
		
		$model = Mage::getModel('demo/cache')
			->getCollection()
			->addFieldToFilter('name', $queue_id);
		
		if (count($model)) {
			$model->getLastItem()->setRegister(serialize($data))->save();
		} else {
			Mage::getModel('demo/cache')->setData(array(
						'name' => $queue_id,
						'register' => serialize($data)
					))->save();
		}
	}
	
	public function prepareBatches($queue_id, $batch_length) {
		$model = Mage::getModel('demo/cache')->getCollection()
			->addFieldToFilter('name', $queue_id)
			->getLastItem();
		
		$register = unserialize($model->getRegister());
		
		$register = array_chunk($register[0], $batch_length);

		$model->setRegister(serialize(array($register)))->save();
	}
	
	public function getBatch($queue_id) {
		$model = Mage::getModel('demo/cache')->getCollection()
			->addFieldToFilter('name', $queue_id)
			->getLastItem();

		$register = unserialize($model->getRegister());
		$batch = array_pop($register[0]);

		$model->setRegister(serialize($register))->save();

		return $batch;
	}
	
	public function push($queue_id, $data) {
		$model = Mage::getModel('demo/cache')->getCollection()
			->addFieldToFilter('name', $queue_id)
			->getLastItem();
		
		$register = unserialize($model->getRegister());

		array_push($register, $data);
		
		$model->setRegister(serialize($register))->save();
	}
	
	public function pushMultiple($queue_id, $data) {
		$model = Mage::getModel('demo/cache')->getCollection()
			->addFieldToFilter('name', $queue_id)
			->getLastItem();
		
		$register = unserialize($model->getRegister());

		//@todo see if this would work with some designated function like merge.
		foreach ($data as $item) {
			array_push($register, $item);
		}

		$model->setRegister(serialize($register))->save();
	}
	
	public function pop($queue_id) {
		$model = Mage::getModel('demo/cache')->getCollection()
			->addFieldToFilter('name', $queue_id)
			->getLastItem();
		
		$register = unserialize($model->getRegister());
	
		$element = array_pop($register);
	
		$model->setRegister(serialize($register))->save();
		
		return $element;
	}
	
	public function getFirst($queue_id) {
		$model = Mage::getModel('demo/cache')->getCollection()
			->addFieldToFilter('name', $queue_id)
			->getLastItem();
		
		$register = unserialize($model->getRegister());

		return $register[0];
	}
	
	public function clean($queue_id) {
		$data = array();
		$model = Mage::getModel('demo/cache')->getCollection()
			->addFieldToFilter('name', $queue_id)
			->getLastItem()
			->setRegister(serialize($data))
			->save();
	}
	
	public function setFirst($queue_id, $replacement) {
		Mage::getModel('demo/cache')
			->getCollection()
			->addFieldToFilter('name', $queue_id)
			->getLastItem()
			->setData('register', serialize(array($replacement)))
			->save();
	}
	
	public function setRegisterData($queue_id, $data) {
		$register = $this->pop($queue_id);
		$register['data'] = $data;
		Mage::helper('demo/queue')->push($queue_id, $register);
	}
}