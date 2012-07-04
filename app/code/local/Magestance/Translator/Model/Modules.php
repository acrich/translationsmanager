<?php

class Magestance_Translator_Model_Modules extends Varien_Object
{

	public function toOptionArray()
	{
		$modules = Mage::getStoreConfig('advanced/modules_disable_output');
		
		$result = array(0 => array('value' => '', 'label' => 'None'));
		foreach ($modules as $name => $disabled) {
			if (!$disabled) {
				if ($name == 'Mage_Admin') {
					$name = 'Mage_Adminhtml';
				}
				$result[] = array('value' => $name, 'label' => $name);
			}
		}
		return $result;
	}
}
