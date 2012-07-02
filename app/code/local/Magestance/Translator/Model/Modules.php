<?php

class Magestance_Translator_Model_Modules extends Varien_Object
{

	public function toOptionArray()
	{
		$modules = Mage::getStoreConfig('advanced/modules_disable_output');
		
		$result = array();
		foreach ($modules as $name => $disabled) {
			if (!$disabled) {
				$result[] = array('value' => $name, 'label' => $name);
			}
		}
		return $result;
	}
}
