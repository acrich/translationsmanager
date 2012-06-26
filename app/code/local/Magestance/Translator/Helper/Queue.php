<?php

class Magestance_Translator_Helper_Queue extends Mage_Core_Helper_Abstract
{	
	public function init($name) {
		$data = array();
		
		$model = Mage::getModel('translator/cache')
			->getCollection()
			->addFieldToFilter('name', $name);
		
		if (count($model)) {
			$model->getLastItem()->setRegister(serialize($data))->save();
		} else {
			Mage::getModel('translator/cache')->setData(array(
						'name' => $name,
						'register' => serialize($data)
					))->save();
		}
	}
	
	public function prepareBatches($name, $batch_length) {
		$model = Mage::getModel('translator/cache')->getCollection()
			->addFieldToFilter('name', $name)
			->getLastItem();
		
		$register = unserialize($model->getRegister());
		
		$register = array_chunk($register[0], $batch_length);

		$model->setRegister(serialize(array($register)))->save();
	}
	
	public function getBatch($name) {
		$model = Mage::getModel('translator/cache')->getCollection()
			->addFieldToFilter('name', $name)
			->getLastItem();

		$register = unserialize($model->getRegister());
		$batch = array_pop($register[0]);

		$model->setRegister(serialize($register))->save();

		return $batch;
	}
	
	public function push($name, $data) {
		$model = Mage::getModel('translator/cache')->getCollection()
			->addFieldToFilter('name', $name)
			->getLastItem();
		
		$register = unserialize($model->getRegister());

		array_push($register, $data);
		
		$model->setRegister(serialize($register))->save();
	}
	
	public function pushMultiple($name, $data) {
		$model = Mage::getModel('translator/cache')->getCollection()
			->addFieldToFilter('name', $name)
			->getLastItem();
		
		$register = unserialize($model->getRegister());

		//@todo see if this would work with some designated function like merge.
		foreach ($data as $item) {
			array_push($register, $item);
		}

		$model->setRegister(serialize($register))->save();
	}
	
	public function pop($name) {
		$model = Mage::getModel('translator/cache')->getCollection()
			->addFieldToFilter('name', $name)
			->getLastItem();
		
		$register = unserialize($model->getRegister());
		$element = array_pop($register);
		$model->setRegister(serialize($register))->save();
		
		return $element;
	}
	
	public function getFirst($name) {
		$model = Mage::getModel('translator/cache')->getCollection()
			->addFieldToFilter('name', $name)
			->getLastItem();
		
		$register = unserialize($model->getRegister());

		return $register[0];
	}
	
	public function clean($name) {
		$data = array();
		$model = Mage::getModel('translator/cache')->getCollection()
			->addFieldToFilter('name', $name)
			->getLastItem()
			->setRegister(serialize($data))
			->save();
	}
	
	public function setFirst($name, $replacement) {
		Mage::getModel('translator/cache')
			->getCollection()
			->addFieldToFilter('name', $name)
			->getLastItem()
			->setData('register', serialize(array($replacement)))
			->save();
	}
	
	public function setRegisterData($name, $data) {
		$register = $this->pop($name);
		$register['data'] = $data;
		Mage::helper('translator/queue')->push($name, $register);
	}
}